INSERT INTO `asst_assets` (`id`, `user_id`, `parent_id`, `location`, `settings_id`, `title`, `description`, `public_status`, `creation_date`, `modification_date`)
VALUES (100, 1, 0, 'http://cashmusic.s3.amazonaws.com/permalink/demos/portugaltheman/WhenTheWarEnds_987yh.mp3', 0, '"When The World Ends" MP3', '320kbps MP3', 1, 1311928519, 0);

INSERT INTO `elmt_elements` (`id`, `user_id`, `name`, `type`, `options`, `license_id`, `creation_date`, `modification_date`)
VALUES (100, 1, 'Portugal. The Man email for download', 'emailcollection', '{"message_invalid_email":"That email address wasn’t valid. Try again.","message_privacy":"We won’t share, sell, or be jerks with your email address. Promise.","message_success":"Thanks! You’re all signed up. Here’s your download:","emal_list_id":"100","asset_id":"100","comment_or_radio":null}', 0, 1295306566, 1315552494);

INSERT INTO `elmt_elements` (`id`, `user_id`, `name`, `type`, `options`, `license_id`, `creation_date`, `modification_date`)
VALUES (101, 1, 'Iron & Wine ticket contest', 'emailcollection', '{"message_invalid_email":"Sorry, that email address wasn’t valid. Please try again.","message_privacy":"Your email won’t be shared and we’ll use it respectfully. ","message_success":"Thanks! You’re all signed up. Winners will be emailed prior to the concert.","emal_list_id":"101","asset_id":"0","comment_or_radio":null}', 0, 1315519963, NULL);

INSERT INTO `user_lists` (`id`, `name`, `description`, `user_id`, `settings_id`, `creation_date`, `modification_date`)
VALUES (100, 'Portugal List', 'The demo list for Portugal. The Man’s email download demo.', 1, 0, 1311316210, 0);

INSERT INTO `user_lists` (`id`, `name`, `description`, `user_id`, `settings_id`, `creation_date`, `modification_date`)
VALUES (101, 'Iron & Wine List', 'The demo list for Iron & Wine’s email contest demo.', 1, 0, 1313045289, 0);