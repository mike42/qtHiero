qtHiero
================================

This is a Qt app for marking up Ancient Egyptian texts in [Manuel de Codage](http://en.wikipedia.org/wiki/Manuel_de_Codage) format.

![qtHiero Screenshot](screenshot.png?raw=true)

Building
--------
To use this app, you need Qt4 and a font which can display Ancient Egyptian Hieroglyphs.

On Debian/Ubuntu, you can get the required packages with:

    apt-get install qt4-dev-tools ttf-ancient-fonts php5-cli
    
You can then clone this repo, build, and run the app with:

    git clone --recursive https://github.com/mike42/qtHiero
    cd qtHiero/
    qmake-qt4
    make
    ./bin/qtHiero

Acknowledgements
----------------
* The [WikiHiero](http://www.mediawiki.org/wiki/Extension:WikiHiero) MediaWiki extension is used to render the selected glyphs.
* Data files are from diverse sources, including WikiHiero, [Unicode documentation](http://www.unicode.org/charts/PDF/U13000.pdf), and [hieroflashcard](http://www.mettetevicomodi.it/hieroflashcard/hfc_index.html)

Building replacement fonts
--------------------------
If you aren't happy with seeing the listings and rendering in two different fonts, or need some extra glyphs, then it's time to try generating a replacement font for wikihiero. The script in src/font will take the manual work out of it.

Make sure you have these:

    apt-get install fontforge imagemagick php5-imagick optipng

And then:

	cd src/font
    make

That script will dump the Aegyptus font (from ttf-ancient-fonts) to a series of EPS files,
then scale them down to small PNG images, and drop them into the Wikihiero folder.

After running this, you can compare the old and new fonts by looking at src/font/compare.html.

### Notes on replacement fonts
* This feature is experimental!
* Allow about an hour for the script to run.
* The prefabricated glyphs are deleted during the repalcement (since they can't be replaced automatically from the font). This fixes a number of bugs.
* You need a spare 200MB of disk space for working with these files.
* The Ca*.png (cartouches) and Ba*.png (formatting marks) are simply put through a transparency filter.

### Importing new glyphs
If you wanted to add the A1A glyph, for example, you could run:

    ./generate.php A1A

After this, they will be available in the qtHiero GUI by typing in "A1A".

### Replacement font bugs to look out for
* There are known issues with N35B, N35C, G10, S28 and the hatching glyph.
* Prefabricated glyphs wont work.
