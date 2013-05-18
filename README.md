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

Building replacement fonts
--------------------------
If you aren't happy with seeing the listings and rendering in different fonts, or need some extra glyphs, then it's time to try generating a replacement font for wikihiero. I've prepared some scripts in src/font which take the manual work out of it.

Make sure you have these:

    apt-get install fontforge imagemagick php5-imagick

And then:

	cd src/font
    make

That script will dump the Aegyptus font (from ttf-ancient-fonts) to a series of EPS files,
then scale them down to small PNG images, then drop them into the Wikihiero folder.

### Notes on replacement fonts
* This feature is experimental!
* The scripts have around 10,000 glyphs to render, which takes an entire day on my hardware.
* The prefabricated glyphs are deleted during the repalcement (since they can't be replaced automatically from the font). This fixes a number of bugs.
* You need a spare 500MB of disk space for working with these files.
* The Ca*.png (cartouches) and Ba*.png (formatting marks) are simply put through a transparency filter.
