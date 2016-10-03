#!/bin/sh

# Auto Sample Docs Generation
npm install groc

# Set identity
git config --global user.email "travis@travis-ci.org"
git config --global user.name "Travis"

# Checkout Master
git fetch; git checkout master;

# Run Docs Generation
# THIS IS DISABLED BECAUSE GROC DOES NOT CREATE NEW LINE AT THE END OF FILE, WHICH RELEASINATOR WANTS.
#cd sample; ../node_modules/groc/bin/groc **/*.php;
# Add and Commit
#git add doc; git commit -m "Updates to Sample Docs"; 
# Push to Master
#git remote add upstream https://${GH_TOKEN}@github.com/paypal/PayPal-PHP-SDK.git > /dev/null
#git push upstream master;
# Back to Home
#cd ..;

# Get ApiGen.phar
wget http://www.apigen.org/apigen.phar

# Generate SDK Docs
php apigen.phar generate --template-theme="bootstrap" -s lib -d ../gh-pages/docs

# Copy Home Page from Master Branch to Gh-Pages folder
cp -r docs/* ../gh-pages/

# Copy samples
cp -r sample ../gh-pages/sample
# As PHP is not allowed in Github
cp sample/index.php ../gh-pages/sample/index.html

cd ../gh-pages

# Add branch
git init
git remote add origin https://${GH_TOKEN}@github.com/paypal/PayPal-PHP-SDK.git > /dev/null
git checkout -B gh-pages

# Push generated files
git add .
git commit -m "Docs updated by Travis"
git push origin gh-pages -fq > /dev/null
