Overview

The Auto Comment Scheduler is a WordPress plugin designed to automatically add relevant comments to existing posts. It allows for scheduled automatic comments, a manual comment trigger, and notifications when comments or usernames run out.

Features
Automated comment insertion on existing published posts.
Manual trigger button for instant comment insertion.
Ensures comments are relevant to post content.
Uses a list of predefined usernames for authenticity.
Prevents duplicate comments by tracking usage.
Notifies the admin when all comments or usernames are exhausted.
Installation & Setup
Download & Install the Plugin

Upload the plugin .zip file to Plugins → Add New → Upload Plugin.
Click Install Now, then Activate.
Configure Settings

Go to Settings → Auto Comment Scheduler.
Enter a list of comments (one per line).
Enter a list of usernames (one per line).
Set the number of comments per day.
Click Save Changes.
How It Works
Automated Comment Insertion (Scheduled Mode)
The plugin automatically inserts comments once per day.
It selects random posts and chooses a relevant comment.
It selects a random username from the list.
Each comment is unique per post.
Manual Comment Insertion
Navigate to Settings → Auto Comment Scheduler.
Click the "Manually Add Comments" button.
The plugin immediately inserts comments without waiting for the daily schedule.
Ensuring Comment Relevance
The plugin analyzes the post content.
It selects a comment that shares words or phrases with the post.
If no relevant comment is found, it falls back to a random comment.
Admin Notification System
If all comments or usernames are used up, the plugin sends an email to the site admin.
The email contains a message prompting the admin to update the lists.
How to Generate Real Usernames and Relevant Comments

To ensure that comments and usernames appear natural, you can use ChatGPT to generate them based on your site's content:

Upload Your Sitemap

Go to your WordPress site and locate your XML sitemap (usually found at yourwebsite.com/sitemap.xml).
Download the sitemap file.
Upload the Sitemap to ChatGPT

Use ChatGPT to analyze the sitemap and extract post titles and topics.
Request Relevant Comments

Ask ChatGPT: "Generate a list of user comments that would naturally fit the topics in my sitemap posts."
Example: If your post is about WordPress SEO, ChatGPT might generate comments like:

"I've been struggling with SEO for a while, but this guide really clarified things! Thanks!"
"Great breakdown! Do you have any tips for optimizing site speed alongside SEO?"
Request Realistic Usernames

Ask ChatGPT: "Generate a list of 200 realistic usernames that look natural for blog comments."
The list should include common first and last names, avoiding generic AI-generated names.
Example usernames:

Michael Stevenson
Lisa Roberts
Jake Mendez
Sophia Green
Copy & Paste the Generated Data

Take the relevant comments and usernames from ChatGPT.
Paste them into the plugin settings under Settings → Auto Comment Scheduler.
Click Save Changes.

Using this method, you ensure that comments appear genuine and relevant to your posts, improving user engagement and site authenticity.

Troubleshooting & FAQs
Q: Comments are not being added. Why?

✅ Solution:

Check if comments are enabled on posts.
Ensure the comments list is not empty.
Try using the manual trigger.
Check the WordPress cron job with WP Crontrol Plugin.
Q: Can I change the frequency of scheduled comments?

✅ Yes! By default, it runs daily, but you can change it to twicedaily or hourly in the code:

wp_schedule_event(time(), 'twicedaily', 'auto_insert_comment_event');
Q: How do I know when comments/usernames run out?

✅ The plugin sends an email notification to the WordPress admin.

Advanced Customization

If you need custom features, you can modify the plugin’s functions:

Change how comments are selected: Modify get_relevant_comments().
Change the email notification recipient: Edit send_admin_notification().
Change the cron schedule: Modify schedule_comment_cron().

For additional help, visit WP Shinobi.

🚀 Enjoy your automated comment system! 🥷
