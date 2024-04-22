<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

class ActivationCest
{
    public function test_it_deactivates_activates_correctly(EndToEndTester $I): void
    {
        $I->loginAsAdmin();
        $I->amOnPluginsPage();

        $I->seePluginActivated('lifterlms');

        $I->deactivatePlugin('lifterlms');

        $I->seePluginDeactivated('lifterlms');

        $I->activatePlugin('lifterlms');

        $I->seePluginActivated('lifterlms');
    }
}
