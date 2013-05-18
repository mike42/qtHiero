#!/bin/bash
# This script has some workarounds
IMG=../../vendor/wikihiero/img	# Path for altering what's in wikihiero

# Remove prefabs and regenerate tables without them
echo "Removing prefabricated glyphs.."
rm -f $IMG/img/*\&*
php script/wikihiero-generateTables.php


cd $IMG
echo "Editing carouches .. "
for f in hiero_Ca*.png
do
    echo "	Editing $f .. "
    convert $f -background none -fuzz 70% -transparent white -flatten $f
done

echo "Editing formatting marks .. "
for f in hiero_Ba*.png
do
    echo "	Editing $f .. "
    convert $f -background none -fuzz 70% -transparent white -flatten $f
done
