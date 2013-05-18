#!/bin/bash

# Configuration
IMG=../../vendor/wikihiero/img	# Path (for altering what's in wikihiero)
FONT=/usr/share/fonts/truetype/ttf-ancient-scripts/Aegyptus313.ttf	# Default Debian path
SIZE=38x38			# Maximum glyph size
DARKEN=no			# Add dark blur to avoid very light glyphs at low resolutions
OPTIMISE=yes		# Pass through optipng (hasn't managed to optimise any of the output files I've seen)
RUN=.generate.run

# Prevent being run twice 
if [ -f $RUN ]; then
	echo "Found $RUN. The process may already be running, or was stopped unexpectedly."
	echo "To fix this, verify that the script is not running, then:"
	echo "	rm $RUN"
	exit
fi
touch $RUN;

# Remove prefabs and regenerate tables
echo "Removing prefabricated glyphs.."
rm -f $IMG/img/*\&*
php script/wikihiero-generateTables.php

# Dump font to EPS files
mkdir -p glyph/temp
if [ "$1" != "resume" ]; then
	echo "Exporting font.."
	cd glyph/temp
	fontforge -lang=ff -script ../../script/export.pe $FONT
	cd ../..
fi

# Scale by percentage so that everything comes out in the right proportion
example="A1_Aegyptus"
echo "Taking measurement of $example..."
source="glyph/eps/A/$example.eps"
dest="test.png"
if [ ! -f "$source" ]; then
	source="glyph/temp/$example.eps"
fi;
convert "$source" "$dest"
SIZE=$(./script/measure.php "$dest" "$SIZE")
echo "Scaling everything to $SIZE of original size..."
rm $dest

# Process in per-letter folders for sanity
dirs=(A B C D E F G H I J K L M N O P Q R S T U V W X Y Z)
for dir in "${dirs[@]}"
do
	# Make source and destination directories
	mkdir -p "glyph/eps/$dir"
	mkdir -p "glyph/png/$dir"
	mkdir -p "glyph/small/$dir/"

	# Move glyphs for this letter in
	mv -f glyph/temp/$dir* "glyph/eps/$dir"

	for f in glyph/eps/$dir/*
	do
		# Check lock file
		if [ ! -f $RUN ]; then
			# This can be used to run the script to be interrupted without leaving 0-byte PNG files around.
			echo "Lock file $RUN was deleted. To pick up where the script stopped, run: "
			echo "	$0 resume"
			exit;
		fi;

		# Figure out directories
		src=$f
		dest=$(basename $src)
		small=glyph/small/$dir/${dest%.*}.png
		echo " * ${dest%.*}"
		dest=glyph/png/$dir/${dest%.*}.png

		# Convert if needed
		if [ ! -f "$dest" ]; then
				echo "	Converting $src to png.. "
				convert $src $dest
		fi

		# Generate small version if needed
		if [ ! -f "$small" ]; then
			# Scale down images
			echo "	Resizing $small .. "
			cp $src $small
			mogrify -resize $SIZE $small

			if [ "$DARKEN" == "yes" ]; then
				# Darken the glyphs for readability
				echo "	Darkening $src .. "
				convert $small \(  +clone \
					  -channel A -blur 0x0.001 -level 0,30% +channel \
					  +level-colors black \
					\) -compose DstOver  -composite $small
			fi

			if [ "$OPTIMISE" == "yes" ]; then
				# Lossless optimisation
				echo "	Optimising $src .. "
				optipng -o7 -quiet $small
			fi
		fi
	done

	# Progress
	echo "Completed letter $dir";
done

# Remove lock file
rm $RUN
