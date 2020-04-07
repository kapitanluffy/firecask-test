<?php
/**
 * Plugin Name: Post Fetcher
 * Description: Fetch posts from an api
 * Version: 1.0
 * Author: Firecask
 * Author URI: https://firecask.com
*/

if (is_admin()) {
    require_once( ABSPATH . 'wp-admin/includes/post.php' );
}

add_action('admin_menu', function () {
    add_options_page('Post Fetcher', 'Post Fetcher 🚚', 'manage_options','post-fetcher-plugin', 'post_fetcher_settings');
});

add_action( 'init', 'post_fetcher_run' );

function post_fetcher_run() {
    if (!is_admin()) return;

    $accessToken = get_option("post_fetcher_access_token");

    if (!$accessToken) {
        $accessToken = post_fetcher_get_access_token();
    }

    if (@$_GET['fetch-posts'] != 1 || !$accessToken) return;
    if (!$accessToken) return;

    $options = get_option("post_fetcher_options");
    $postsUrl = sprintf("%s/posts", $options['api_url']);

    $response = wp_remote_request($postsUrl, [
        'method' => 'GET',
        'headers' => ['Authorization' => "Bearer $accessToken"]
    ]);

    $response['body'] = json_decode($response['body'], true);

    foreach ($response['body']['data'] as $post) {
        $postId = post_exists($post['title'], $post['content']);

        if ($postId > 0)  {
            $post_ = get_post($postId);

            if ($post_->post_status == 'trash'){
                wp_untrash_post($postId);
            }

            wp_update_post([
                'post_id' => $postId,
                'post_title' => $post['title'],
                'post_content' => $post['content'],
            ]);
        }

        if ($postId == 0)  {
            wp_insert_post([
                'post_title' => $post['title'],
                'post_content' => $post['content'],
            ]);
        }
    }
};

function post_fetcher_get_access_token() {
    $options = get_option("post_fetcher_options");
    if (!$options) return false;

    $accessToken = get_option("post_fetcher_access_token");

    if (!$accessToken) {
        $accessTokenUrl = sprintf("%s/login", $options['api_url']);

        $response = wp_remote_request($accessTokenUrl, [
            'method' => 'POST',
            'body' => [
                'client_id' => $options['client_id'],
                'client_secret' => $options['client_secret'],
                'grant_type' => 'client_credentials',
            ]
        ]);

        if (is_wp_error($response)) {
            error_log(serialize($response));
            return false;
        }

        $body = json_decode($response['body'], true);
        add_option('post_fetcher_access_token', $body['access_token']);
    }

    return $accessToken;
}

add_action('updated_option_post_fetcher_options', function($old, $new) {
    error_log(sprintf("%s", "options updated"));
    post_fetcher_get_access_token();
}, 10, 2);

function post_fetcher_settings() {
    $accessToken = get_option("post_fetcher_access_token");
    ?>
    <h2>Post Fetcher Settings</h2>
    <form action="options.php" method="post">
        <?php
        settings_fields('post_fetcher_options');
        do_settings_sections('post_fetcher_plugin');
        ?>
        <input name="submit" class="button button-primary" type="submit" value="<?php esc_attr_e('Save'); ?>" />

        <?php if ($accessToken): ?>
        <a class="button button-primary" type="button" href="?page=post-fetcher-plugin&fetch-posts=1"><?php esc_attr_e('Fetch Posts'); ?></a>
        <?php endif; ?>
    </form>
    <?php
}

function register_settings() {
    register_setting( 'post_fetcher_options', 'post_fetcher_options');

    add_settings_section('api_settings', null, null, 'post_fetcher_plugin');

    add_settings_field('post_fetcher_api_url', 'API URL', function () {
        $options = get_option( 'post_fetcher_options' );
        $value = esc_attr($options['api_url']);
        echo "<input id='post_fetcher_api_url' name='post_fetcher_options[api_url]' type='text' value='{$value}' />";
    }, 'post_fetcher_plugin', 'api_settings');

    add_settings_field('post_fetcher_client_id', 'Client ID', function () {
        $options = get_option( 'post_fetcher_options' );
        $value = esc_attr($options['client_id']);
        echo "<input id='post_fetcher_client_id' name='post_fetcher_options[client_id]' type='text' value='{$value}' />";
    }, 'post_fetcher_plugin', 'api_settings');

    add_settings_field( 'post_fetcher_client_secret', 'Client Secret', function () {
        $options = get_option( 'post_fetcher_options' );
        $value = esc_attr($options['client_secret']);
        echo "<input id='post_fetcher_client_secret' name='post_fetcher_options[client_secret]' type='text' value='{$value}' />";
    }, 'post_fetcher_plugin', 'api_settings' );
}
add_action('admin_init', 'register_settings');






