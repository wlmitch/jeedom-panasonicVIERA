#!/bin/bash

touch /tmp/dependancy_panasonicVIERA_in_progress
echo "Launch install of PanasonicViera dependancy"

echo 50 > /tmp/dependancy_panasonicVIERA_in_progress
echo 'Install panasonic-viera library'
pip3 install panasonic-viera

echo 100 > /tmp/dependancy_panasonicVIERA_in_progress
echo "Everything is successfully installed!"
rm /tmp/dependancy_panasonicVIERA_in_progress
