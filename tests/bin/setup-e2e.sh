#!/bin/bash

llmsenv=vendor/bin/llms-env

# 1. Activate LifterLMS plugin
##############################
$llmsenv wp plugin activate lifterlms


# 2. Bootstrap user accounts
############################

# StudentDashboard/RedeemVoucher
$llmsenv wp user create voucher voucher@email.tld --role=student --user_pass=password

# StudentDashboardLogin -> should allow a user with valid credentials to login
# Settings/CopyPrevention -> StudentUser
$llmsenv wp user create validcreds validcreds@email.tld --role=student --user_pass=password


# 3. Set options.
#################

$llmsenv wp option update can_compress_scripts 1


# 4. Bootstrap posts
####################

# Settings/CopyPrevention
COPY_TEST_ID=$( $llmsenv wp post create --post_type=page --post_title="Integrity-Test" --post_status=publish --porcelain )
$llmsenv wp media import https://raw.githubusercontent.com/gocodebox/lifterlms/trunk/tests/assets/yura-timoshenko-R7ftweJR8ks-unsplash.jpeg --post_id=$COPY_TEST_ID --featured_image
