#!/bin/bash

# Run PHPCBF to automatically fix coding standards
./vendor/bin/phpcbf --standard=Drupal,DrupalPractice --extensions=php,module,inc,install,test,profile,theme,css,info,txt,md,yml src/ modules/ tests/

# Stage the fixed files
git add src/ modules/ tests/

# Run PHPCS to check if any issues remain
./vendor/bin/phpcs --standard=Drupal,DrupalPractice --extensions=php,module,inc,install,test,profile,theme,css,info,txt,md,yml src/ modules/ tests/
