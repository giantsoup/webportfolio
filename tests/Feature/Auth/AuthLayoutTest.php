<?php

test('the login page uses the portfolio branded back office layout', function () {
    $this->get(route('login'))
        ->assertSuccessful()
        ->assertSee('class="admin ', false)
        ->assertSee('aria-label="Taylor Portfolio, home"', false)
        ->assertSeeInOrder(['Back office', 'Log in to your account'])
        ->assertSee('Back to the site')
        ->assertSee(route('home'), false);
});
