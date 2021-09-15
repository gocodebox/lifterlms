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
$llmsenv wp user create validcreds validcreds@email.tld --role=student --user_pass=password


# 3. Set options.
#################

$llmsenv wp option update can_compress_scripts 1
