<?php
/**
 * Plugin Name: Shinobi Auto Comment Scheduler
 * Plugin URI: https://wpshinobi.com
 * Description: Automatically inserts relevant comments on existing WordPress posts every day based on user-defined settings. Now includes a manual trigger button, ensures comment relevance, and sends notifications when comments or usernames are exhausted.
 * Version: 2.2
 * Author: WP Shinobi
 * Author URI: https://wpshinobi.com
 * License: GPL2
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Auto_Comment_Scheduler {
    private $comments_list;
    private $usernames_list;
    private $comments_per_day;

    public function __construct() {
        add_action('admin_menu', [$this, 'create_settings_page']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('wp', [$this, 'schedule_comment_cron']);
        add_action('auto_insert_comment_event', [$this, 'insert_scheduled_comments']);
        add_action('admin_post_manual_comment_trigger', [$this, 'manual_comment_trigger']);
    }

    public function create_settings_page() {
        add_options_page(
            'Shinobi Auto Comment Scheduler Settings',
            'Shinobi Auto Comment Scheduler',
            'manage_options',
            'auto-comment-scheduler',
            [$this, 'settings_page_content']
        );
    }

    public function register_settings() {
        register_setting('auto_comment_scheduler_settings', 'auto_comment_scheduler_comments', ['sanitize_callback' => [$this, 'sanitize_textarea']]);
        register_setting('auto_comment_scheduler_settings', 'auto_comment_scheduler_usernames', ['sanitize_callback' => [$this, 'sanitize_textarea']]);
        register_setting('auto_comment_scheduler_settings', 'auto_comment_scheduler_comments_per_day', ['default' => 5, 'sanitize_callback' => 'absint']);
    }

    public function sanitize_textarea($input) {
        $lines = explode("\n", trim($input));
        return array_map('sanitize_text_field', $lines);
    }

    public function settings_page_content() {
        ?>
        <div class="wrap">
            <h2>Shinobi Auto Comment Scheduler Settings</h2>
            <form method="post" action="options.php">
                <?php
                settings_fields('auto_comment_scheduler_settings');
                do_settings_sections('auto_comment_scheduler_settings');
                ?>
                <label for="auto_comment_scheduler_comments">Comment List (one per line):</label><br>
                <textarea name="auto_comment_scheduler_comments" rows="10" cols="50"><?php echo esc_textarea(implode("\n", (array) get_option('auto_comment_scheduler_comments', []))); ?></textarea><br>
                <label for="auto_comment_scheduler_usernames">Username List (one per line):</label><br>
                <textarea name="auto_comment_scheduler_usernames" rows="5" cols="50"><?php echo esc_textarea(implode("\n", (array) get_option('auto_comment_scheduler_usernames', []))); ?></textarea><br>
                <label for="auto_comment_scheduler_comments_per_day">Number of Comments Per Day:</label><br>
                <input type="number" name="auto_comment_scheduler_comments_per_day" value="<?php echo esc_attr(get_option('auto_comment_scheduler_comments_per_day', 5)); ?>" min="1"><br>
                <?php submit_button(); ?>
            </form>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php?action=manual_comment_trigger')); ?>">
                <?php submit_button('Manually Add Comments'); ?>
            </form>
        </div>
        <?php
    }

    public function schedule_comment_cron() {
        if (!wp_next_scheduled('auto_insert_comment_event')) {
            wp_schedule_event(time(), 'daily', 'auto_insert_comment_event');
        }
    }

    public function manual_comment_trigger() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized action.', 'auto-comment-scheduler'));
        }
        $this->insert_scheduled_comments();
        wp_redirect(admin_url('options-general.php?page=auto-comment-scheduler&success=1'));
        exit;
    }

    public function insert_scheduled_comments() {
        $this->load_settings();
        
        if (empty($this->comments_list) || empty($this->usernames_list)) {
            error_log('Shinobi Auto Comment Scheduler: No comments or usernames available.');
            $this->send_admin_notification();
            return;
        }
        
        $args = [
            'post_status' => 'publish',
            'orderby' => 'rand',
            'posts_per_page' => $this->comments_per_day,
        ];
        $posts = get_posts($args);
        
        $used_comments = [];
        $used_usernames = [];
        
        foreach ($posts as $post) {
            $available_comments = $this->get_relevant_comments($post->post_content, $used_comments);
            $available_usernames = array_diff($this->usernames_list, $used_usernames);
            
            if (empty($available_comments) || empty($available_usernames)) {
                $this->send_admin_notification();
                break;
            }
            
            $selected_comment = array_rand(array_flip($available_comments));
            $selected_username = array_rand(array_flip($available_usernames));
            
            $used_comments[] = $selected_comment;
            $used_usernames[] = $selected_username;
            
            if ($selected_comment && $selected_username) {
                $comment_data = [
                    'comment_post_ID' => $post->ID,
                    'comment_author' => sanitize_text_field($selected_username),
                    'comment_author_email' => sanitize_email(strtolower(str_replace(' ', '', $selected_username)) . '@example.com'),
                    'comment_content' => sanitize_text_field($selected_comment),
                    'comment_approved' => 1,
                ];
                wp_insert_comment($comment_data);
            }
        }
    }

    private function send_admin_notification() {
        $admin_email = get_option('admin_email');
        $subject = 'Shinobi Auto Comment Scheduler Notification';
        $message = 'The Shinobi Auto Comment Scheduler has run out of comments or usernames. Please update the lists in the plugin settings.';
        wp_mail($admin_email, $subject, $message);
    }

    private function get_relevant_comments($post_content, $used_comments) {
        $post_words = array_filter(explode(' ', strtolower(strip_tags($post_content))));
        $relevant_comments = [];
        
        foreach ($this->comments_list as $comment) {
            foreach (explode(' ', strtolower($comment)) as $word) {
                if (in_array($word, $post_words) && !in_array($comment, $used_comments)) {
                    $relevant_comments[] = $comment;
                    break;
                }
            }
        }
        return !empty($relevant_comments) ? $relevant_comments : array_diff($this->comments_list, $used_comments);
    }

    private function load_settings() {
        $this->comments_list = (array) get_option('auto_comment_scheduler_comments', []);
        $this->usernames_list = (array) get_option('auto_comment_scheduler_usernames', []);
        $this->comments_per_day = (int) get_option('auto_comment_scheduler_comments_per_day', 5);
    }
}

new Auto_Comment_Scheduler();
