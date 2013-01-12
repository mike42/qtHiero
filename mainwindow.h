#include "ui_mainwindow.h"

#include <stdint.h>
#include <iostream>
#include <fstream>
#include <cstdlib>
#include <vector>
#include <map>
#include <string>
#include <QString>

struct Glyph {
	uint32_t unicodeChar;
	QString gardinerCode;
	QString desc;

	QString translitMdC;
	QString translitAllen;
	QString translitGardiner;

	QString audioFilename;
};

struct GardinerCategory {
	GardinerCategory() { };
	GardinerCategory(QString newCategoryName) : categoryName(newCategoryName) { };
	QString categoryName;
	QStringList glyphList;
};

class MainWindow : public QMainWindow {
	Q_OBJECT

	public:
		MainWindow(QWidget *parent = 0, Qt::WindowFlags flags = 0);
		void loadGardinerSigns(const char* categoryFilename, const char* signFilename);
		void loadLiterals(const char* literalsFilename);

	protected slots:
		void updateGardinerCategory(int itemIndex);
		void addGardinerSign(QModelIndex itemIndex);
		void addUniliteralSign(QModelIndex itemIndex);
		void addBiliteralSign(QModelIndex itemIndex);
		void addTriliteralSign(QModelIndex itemIndex);

	private:
		Ui::MainWindow ui;
		QMap<QString, Glyph> glyph;
		QMap<QString, GardinerCategory> gardinerSignCategory;
		QStringList uniliteralStringList, biliteralStringList, triliteralStringList;
		QString unicode2qstr(uint32_t character);

		QMap<QString, GardinerCategory> loadGardinerSignsFromFile(const char* categoryFilename, const char* signFilename);
		void loadLiteralsFromFile(const char* literalsFilename);
		void appendGlyphTranslit(QString glyphStr);
};
