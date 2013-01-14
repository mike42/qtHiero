#include "mainwindow.h"

#include <QMessageBox>
#include <QStringListModel>

using namespace std;

/**
 * Return a QString from a unicode code-point
 **/
QString MainWindow :: unicode2qstr(uint32_t character) {
	if(0x10000 > character) {
		/* BMP character. */
		return QString(QChar(character));
	} else if (0x10000 <= character) {
		/* Non-BMP character, return surrogate pair */
		unsigned int code;
		QChar glyph[2];
		code = (character - 0x10000);
		glyph[0] = QChar(0xD800 | (code >> 10));
		glyph[1] = QChar(0xDC00 | (code & 0x3FF));
		return QString(glyph, 2);
	}
	/* character > 0x10FFF */
	return QString("");
}

/**
 * Load all of the gardiner signs from data files
 **/
QMap<QString, GardinerCategory> MainWindow :: loadGardinerSignsFromFile(const char* categoryFilename, const char* signFilename) {
    /* A couple of variables needed to parse */
    string line;
    size_t tab, len, nextTab, lineNo;
    bool fail;
    QMap<QString, GardinerCategory> category;

    /* Load garder sign categories */
    ifstream fileCategory (categoryFilename, ifstream::in);
    if(!fileCategory.is_open()) {
        cerr << "Warning: Failed to open " << categoryFilename << endl;
        return category;
    }
    QString categoryCode, categoryName;
    lineNo = 0;
    while(getline(fileCategory, line)) {
        lineNo++;
        len = line.length();
        if(line.find_first_of('#') != 0 && len != 0) {
            /* Only process non-empty lines which do not begin with a # */
            tab = line.find_first_of('\t');
            categoryCode = QString::fromStdString(line.substr(0, tab));
            categoryName = QString::fromStdString(line.substr(tab + 1, len - tab - 1));
            category[categoryCode] = GardinerCategory(categoryName);
        }
    }
    fileCategory.close();

    /* Load signs themselves and put them into categories */
    ifstream fileSigns (signFilename, ifstream::in);
	Glyph* newGlyph;
    if(!fileSigns.is_open()) {
        cerr << "Warning: Failed to open " << signFilename << endl;
        return category;
    }
    QString gardinerCode, glyphStr, translitMdC;
    uint32_t unicodeChar;
    lineNo = 0;
    while(getline(fileSigns, line)) {
        lineNo++;
        len = line.length();
        fail = false;
        if(line.find_first_of('#') != 0 && len != 0) {
            /* Only process non-empty lines which do not begin with a # */
            tab = 0;
            nextTab = line.find_first_of('\t');
            if(nextTab != string::npos) {
                categoryCode = QString::fromStdString(line.substr(tab, nextTab - tab));
            } else {
                fail = true;
            }

            if(!fail) {
                tab = nextTab + 1;
                nextTab = line.find_first_of('\t', tab);
                if(nextTab != string::npos) {
                    gardinerCode = QString::fromStdString(line.substr(tab, nextTab - tab));
                } else {
                    fail = true;
                }
            }

            if(!fail) {
                tab = nextTab + 1;
                nextTab = line.find_first_of('\t', tab);
                if(nextTab != string::npos) {
                    /* Load unicode char from hex as uint32_t */
                    unicodeChar = (uint32_t) strtol(line.substr(tab, len - tab).c_str(), NULL, 16);
					glyphStr = unicode2qstr(unicodeChar);
                } else {
					fail = true;
                }
            }

 			if(!fail) {
                tab = nextTab + 1;
                nextTab = line.find_first_of('\t', tab);
                if(nextTab != string::npos) {
					fail = true;
                } else {
					/* Load MdC transliteration */
                    translitMdC = QString::fromStdString(line.substr(tab, nextTab - tab));
                }
            }


            /* Add to collection */
            if(fail) {
                cerr << "Problem reading signs file on line " << lineNo << ":" << "\n" << line << "\n";
            } else { 
                /* Add sign to category */
                if(category.count(categoryCode) > 0) {
					/* Create glyph and add to category */
					newGlyph = new Glyph();
					newGlyph -> unicodeChar = unicodeChar;
					newGlyph -> gardinerCode = gardinerCode;
					newGlyph -> translitMdC = translitMdC;
					glyph[glyphStr] = *newGlyph;

					category[categoryCode].glyphList << glyphStr;
                } else {
                    cerr << "Category code " << categoryCode.toUtf8().constData() << " is not defined\n";
                }
            }
        }
    }
    fileSigns.close();

    /* Output some statistics */
   	QMap<QString, GardinerCategory>::iterator it;
    cout << "Loaded Gardiner sign list:" << endl;
    for(it = category.begin(); it != category.end(); it++) {
		/* Show category code, name and sizes */
		cout << " - " << it.key().toUtf8().constData() << " => " <<
			it.value().categoryName.toUtf8().constData() << 
			" (" << it.value().glyphList.size() << " signs)\n";
    }
    return category;
}

/**
 * Call loadGardinerSignsFromFile() and process results
 **/
void MainWindow :: loadGardinerSigns(const char* categoryFilename, const char* signFilename) {
	/* Fill Gardiner list */
	ui.cboGardinerCategory -> addItem("(select category)", "");

	gardinerSignCategory = loadGardinerSignsFromFile(categoryFilename, signFilename);
	QMap<QString, GardinerCategory>::iterator it;

	QString label;
	for(it = gardinerSignCategory.begin(); it != gardinerSignCategory.end(); it++) {
		/* Convert to QStrings */
		label = it.key() + ". " + it.value().categoryName;
		ui.cboGardinerCategory -> addItem(label, it.key());
	}
	ui.cboGardinerCategory -> setCurrentIndex(0);
}

/** 
 * Set up the window
 */
MainWindow :: MainWindow(QWidget *parent, Qt::WindowFlags flags)
	: QMainWindow(parent, flags) {
	ui.setupUi(this);

	connect(ui.cboGardinerCategory, SIGNAL(currentIndexChanged (int)), this, SLOT(updateGardinerCategory(int)));
	connect(ui.listGardiner, SIGNAL(doubleClicked (QModelIndex)), this, SLOT(addGardinerSign(QModelIndex)));
	connect(ui.listUniliteral, SIGNAL(doubleClicked (QModelIndex)), this, SLOT(addUniliteralSign(QModelIndex)));
	connect(ui.listBiliteral, SIGNAL(doubleClicked (QModelIndex)), this, SLOT(addBiliteralSign(QModelIndex)));
	connect(ui.listTriliteral, SIGNAL(doubleClicked (QModelIndex)), this, SLOT(addTriliteralSign(QModelIndex)));
	connect(ui.plainTextEdit, SIGNAL(textChanged ()), this, SLOT(refreshWebView () ));

}

/**
 * Re-load the list of signs when the category changes
 **/
void MainWindow :: updateGardinerCategory(int itemIndex) {
	/* Get category as string */
	QString category = ui.cboGardinerCategory -> itemData(itemIndex).toString();
	cout << category.toUtf8().constData();

	QStringListModel *glyphModel = new QStringListModel();
	QStringList glyphList;
	if(gardinerSignCategory.count(category) > 0) {
		/* Only populate a list if needed */
		glyphList = gardinerSignCategory[category].glyphList;
	}

	/* Set model and delete old one */
	glyphModel -> setStringList(glyphList);
	QAbstractItemModel *m = ui.listGardiner -> model();
	ui.listGardiner -> setModel(glyphModel);
	delete m;
}

/**
 * Insert an item from the gardiner list
 **/
void MainWindow :: addGardinerSign(QModelIndex itemIndex) {\
	QString glyphStr = ui.listGardiner -> model() -> data(itemIndex).toString();
	appendGlyphTranslit(glyphStr);
}

/**
 * Insert an item from the uniliteral list
 **/
void MainWindow :: addUniliteralSign(QModelIndex itemIndex) {
	QString glyphStr = ui.listUniliteral -> model() -> data(itemIndex).toString();
	appendGlyphTranslit(glyphStr);
}

/**
 * Insert an item from the biliteral list
 **/
void MainWindow :: addBiliteralSign(QModelIndex itemIndex) {
	QString glyphStr = ui.listBiliteral -> model() -> data(itemIndex).toString();
	appendGlyphTranslit(glyphStr);
}

/**
 * Insert an item from the triliteral list
 **/
void MainWindow :: addTriliteralSign(QModelIndex itemIndex) {
	QString glyphStr = ui.listTriliteral -> model() -> data(itemIndex).toString();
	appendGlyphTranslit(glyphStr);
}

/**
 * Append the MdC transliteration of a glyph to the box
 **/
void MainWindow :: appendGlyphTranslit(QString glyphStr) {
	if(glyph.count(glyphStr) <= 0) {
		cerr << "Selected glyph does not exist." << endl;
		return;
	}

	/* Add MdC transliteration to box */
	QString translit = glyph[glyphStr].translitMdC;
	ui.plainTextEdit -> textCursor().insertText(translit);
	ui.plainTextEdit -> setFocus();
}

/**
 * Populate lists of literals in UI.
 **/
void MainWindow :: loadLiterals(const char* literalsFilename) {
	loadLiteralsFromFile(literalsFilename);
	ui.listUniliteral -> setModel(new QStringListModel(uniliteralStringList));
	ui.listBiliteral -> setModel(new QStringListModel(biliteralStringList));
	ui.listTriliteral -> setModel(new QStringListModel(triliteralStringList));
}

/**
 * Load literals file and store in uniliteralStringList, biliteralStringList, triliteralStringList
 */
void MainWindow :: loadLiteralsFromFile(const char* literalsFilename) {
	/* Open literals file */
	ifstream fileLiterals (literalsFilename, ifstream::in);
    if(!fileLiterals.is_open()) {
        cerr << "Warning: Failed to open " << literalsFilename << endl;
        return;
    }

	uint32_t unicodeChar;
	QString glyphStr;
	size_t tab, len, nextTab, lineNo;
	bool fail;
	string line;

	/* Read each line */
    lineNo = 0;
	while(getline(fileLiterals, line)) {
		lineNo++;
		len = line.length();
		fail = false;
        if(line.find_first_of('#') != 0 && len != 0) {
            /* Only process non-empty lines which do not begin with a # */
			tab = 0;
			nextTab = line.find_first_of('\t');
			if(nextTab != string::npos) {
				/* Load glyph as string */
				unicodeChar = (uint32_t) strtol(line.substr(tab, len - tab).c_str(), NULL, 16);
				glyphStr = unicode2qstr(unicodeChar);
			} else {
				fail = true;
			}

			if(!fail) {
				/* Check if sign is uniliteral */
				tab = nextTab + 1;
				nextTab = line.find_first_of('\t', tab);
				if(nextTab != string::npos) {	
					if(line.substr(tab, nextTab - tab) == "1") {
						/* Add to uniliteral list */
						uniliteralStringList << glyphStr;
					}
				} else {
					fail = true;
				}
			}

			if(!fail) {
				/* Check if sign is biliteral */
				tab = nextTab + 1;
				nextTab = line.find_first_of('\t', tab);
				if(nextTab != string::npos) {	
					if(line.substr(tab, nextTab - tab) == "1") {
						/* Add to biliteral list */
						biliteralStringList << glyphStr;
					}
				} else {
					fail = true;
				}
			}

			if(!fail) {
				/* Check if sign is triliteral */
				tab = nextTab + 1;
				nextTab = line.find_first_of('\t', tab);
				if(nextTab == string::npos) {	
					if(line.substr(tab, nextTab - tab) == "1") {
						/* Add to triliteral list */
						triliteralStringList << glyphStr;
					}
				} else {
					/* Too many fields on line */
					fail = true;
				}
			}

			/* if the wrong number of columns were found */
			if(fail) {
                cerr << "Problem reading literals file on line " << lineNo << ":" << "\n" << line << "\n";
			}
		}
	}
}

/** 
 * Re-generate the rendered hieroglyphs (eg after a change)
 **/
void MainWindow :: refreshWebView() {
	// TODO: Render glypghs in a separate thread
	/* QProcess render;
	render.start("render.php");
	render.write(ui.plainTextEdit -> toPlainText());
	render.write("\n");
	more.closeWriteChannel(); */
	ui.webView -> setHtml(QString::fromStdString("<html><body><p>") + ui.plainTextEdit -> toPlainText() + QString::fromStdString("</p></body></html>"));
}
