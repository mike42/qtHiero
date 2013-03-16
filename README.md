qtHiero
================================

This is a Qt app for marking up Ancient Egyptian texts in [Manuel de Codage](http://en.wikipedia.org/wiki/Manuel_de_Codage) format.

Building
--------
To use this app, you need Qt4 and a font which can display Hieroglyphs.

On Debian/Ubuntu, you can get the required packages with:

    apt-get install qt4-dev-tools ttf-ancient-fonts
    
You can then clone this repo, build, and run the app with:

    git clone https://github.com/mike42/qtHiero
    cd qtHiero/
    qmake
    make
    ./bin/qtHiero
