<?php

namespace App\Controllers;

use Sober\Controller\Controller;

class App extends Controller
{
    public function siteName()
    {
        return get_bloginfo('name');
    }

    public static function title()
    {
        if (is_home()) {
            if ($home = get_option('page_for_posts', true)) {
                return get_the_title($home);
            }
            return __('Latest Posts', 'sage');
        }
        if (is_archive()) {
            return get_the_archive_title();
        }
        if (is_search()) {
            return sprintf(__('Search Results for %s', 'sage'), get_search_query());
        }
        if (is_404()) {
            return __('Not Found', 'sage');
        }
        return get_the_title();
    }

    public static function like_or_not()
    {
        $result = [];
        if (get_field('like_or_not') == 'No')
        {
            $result['message'] = get_field('message');
            $result['level'] = get_field('level');
            return $result;
        }
        return 0;
    }

    public static function extra_btn()
    {
        $result = [];
        $result['text'] = get_field('button_text');
        if ($result['text'] == NULL)
            $result['text'] = 'CLICK ME';

        $result['default_bg'] = get_field('use_default_bg');
        if ($result['default_bg'] == NULL)
            $result['default_bg'] = 'default';

        if (get_field('use_default_bg') == 'custom')
        {
            $result['bg'] = get_field('bg_color');
        }
        return $result;
    }

    public static function carousel()
    {
        $result = [];
        $cnt = 0;
        $result['logo'][] = get_field('logo1');
        $result['text'][] = get_field('logo_1_text');

        if ($result['logo'][$cnt] != NULL)
        {
            $cnt++;
            $result['logo'][] = get_field('logo2');
            $result['text'][] = get_field('logo_2_text');            

            if ($result['logo'][$cnt] != NULL) {
                $cnt++;
                $result['logo'][] = get_field('logo3');
                $result['text'][] = get_field('logo_3_text');
                if ($result['logo'] != NULL)
                    $cnt++;
            }
        }

        $result['count'] = $cnt;

        return $result;
    }
}
