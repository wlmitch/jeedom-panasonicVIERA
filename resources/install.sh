#!/bin/bash

touch /tmp/dependency_panasonicVIERA_in_progress
echo 'Launch install of PanasonicViera dependency'

echo 50 > /tmp/dependency_panasonicVIERA_in_progress
echo 'Install panasonic-viera library'
pip3 install panasonic-viera aiohttp

echo 100 > /tmp/dependency_panasonicVIERA_in_progress
echo 'Everything is successfully installed!'
rm /tmp/dependency_panasonicVIERA_in_progress
