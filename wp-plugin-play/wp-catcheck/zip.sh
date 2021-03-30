mv catcheck.zip catcheck-previous.zip
cd catcheck
zip -x '*.git*' -r ../catcheck.zip ./
cd ..
