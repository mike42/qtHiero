#include <QApplication>
#include "mainwindow.h"

int main(int argc, char *argv[]) {
   	QApplication app(argc, argv);

	MainWindow window;
	window.loadGardinerSigns("../data/gardiner-category.txt", "../data/gardiner-signs.txt");
	window.loadLiterals("../data/wp-literals.txt");
	window.show();

	return app.exec();
}
