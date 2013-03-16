qtHiero
================================

This is a Qt app for marking up Ancient Egyptian texts in [Manuel de Codage](http://en.wikipedia.org/wiki/Manuel_de_Codage) format.

Building
--------
To use this app, you need Qt4 and a font which can display Ancient Egyptian Hieroglyphs.

On Debian/Ubuntu, you can get the required packages with:

    apt-get install qt4-dev-tools ttf-ancient-fonts php5-cli
    
You can then clone this repo, build, and run the app with:

    git clone --recursive https://github.com/mike42/qtHiero
    cd qtHiero/
    qmake
    make
    ./bin/qtHiero

Acknowledgements
----------------
* The [WikiHiero](http://www.mediawiki.org/wiki/Extension:WikiHiero) MediaWiki extension is used to render the selected glyphs.
* Data files are from diverse sources, including WikiHiero, [Unicode documentation](http://www.unicode.org/charts/PDF/U13000.pdf), and [hieroflashcard](http://www.mettetevicomodi.it/hieroflashcard/hfc_index.html)
