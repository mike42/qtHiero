#include <QApplication>
#include "mainwindow.h"

int main(int argc, char *argv[]) {
   	QApplication app(argc, argv);

	MainWindow window;
	window.loadGardinerSigns("gardiner-category.txt", "gardiner-signs.txt");
	window.loadLiterals("wp-literals.txt");
	window.show();

	return app.exec();
}
