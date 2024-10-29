<?php
/**
 * The Twitter related functionality of the plugin.
 *
 * @link       https://cba-records.com
 * @since      1.0.0
 *
 * @package    Twitter_Api_Fetch
 * @subpackage Twitter_Api_Fetch/admin
 */

require dirname( __FILE__, 2 ) . '/vendor/autoload.php';

use Abraham\TwitterOAuth\TwitterOAuth;

/**
 * The Twitter related functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Api_Fetch_Twitter
 * @subpackage Api_Fetch_Twitter/admin
 * @author     Matthew Lee <info@cba-records.com>
 */
class Api_Fetch_Twitter_Admin_Twitter {

	/**
	 *  Validate Twitter user credentials.
	 *
	 *  @since 1.0.0
	 *  @param Array $input Form Input submitted on update of Twitter API details.
	 *  @return String Message from The Twitter API.
	 */
	public static function validate_api_details( $input ) {

		$status = 200;

		$connection = new TwitterOAuth( $input['api_key'], $input['api_secret'], $input['access_token'], $input['access_secret'] );
		$connection->setApiVersion( '2' );
		$content = $connection->get( 'users/by/username/' . $input['user_name'] );

		if ( null !== $content ) {

			if ( ! isset( $content->data ) && isset( $content->status ) ) {

				$status  = $content->status;
				$message = $content->detail;

			} elseif ( ! isset( $content->data ) && isset( $content->errors ) ) {

				$message = $content->errors[0]->detail;
				$status  = 404;

			} else {

				$message = $content->data->id;

			}
		} else {
			$status  = 301;
			$message = __( 'Please Add API details or/and a User Name', 'api-fetch-twitter' );
		}

		return array(
			'status'  => $status,
			'message' => $message,
		);

	}

	/**
	 *  Create XML feed from Twitter JSON.
	 *
	 *  @since 1.0.0
	 *  @param Array $input Twitter APi Fetch Options.
	 */
	public static function create_twitter_xml( $input ) {

		$xml        = new SimpleXMLElement( '<?xml version="1.0" encoding="utf-8"?><data></data>' );
		$connection = new TwitterOAuth( $input['api_key'], $input['api_secret'], $input['access_token'], $input['access_secret'] );
		$connection->setApiVersion( '2' );

		$user   = self::user_object( $connection, $input['user_id'] );
		$tweets = $connection->get(
			'users/' . $input['user_id'] . '/tweets',
			array(
				'max_results'  => 30,
				'tweet.fields' => 'entities,attachments,created_at',
				'expansions'   => 'attachments.media_keys',
			)
		);

		if ( null !== $tweets ) {

			if ( count( $tweets->data ) > 0 ) {

				foreach ( $tweets->data as $tweet ) {

					$id    = $tweet->id;
					$text  = $tweet->text;
					$date  = $tweet->created_at;
					$regex = '|RT @.*:|';

					if ( ! preg_match( $regex, $text ) ) {

						$text = self::parse_tweet( $text );

						$twt = $xml->addChild( 'item' );
						$twt->addChild( 'photo', $user->profile_image_url );
						$twt->addChild( 'date', self::process_date( $date ) );
						self::add_cdata( 'user', $user->screen_name, $twt );
						self::add_cdata( 'name', $user->name, $twt );
						$text = self::add_cdata( 'text', trim( $text ), $twt );
						$text->addAttribute( 'type', 'Tweet' );
						$text->addAttribute( 'id', $id );

						if ( isset( $tweet->attachments ) ) {

							$media_key = $tweet->attachments->media_keys[0];
							$media     = self::tweet_media( $connection, $media_key, $id );

							if ( isset( $tweet->entities->urls ) ) {

								$url = $tweet->entities->urls[0]->url;

							}

							$media = $twt->addChild( 'media', $media[1] );
							$media->addAttribute( 'media_type', $media[0] );
							$media->addAttribute( 'url', $url );

						}
					} else {

						self::process_retweet( $connection, $tweet, $user->name, $xml );

					}
				}
					$xml->asXml( dirname( __FILE__, 2 ) . '/public/feeds/twitter-' . get_current_blog_id() . '.xml' );
			}
		}

	}

	/**
	 *  Get user info.
	 *
	 *  @since 1.0.0
	 *
	 *  @param object $connection Connection to Twitter.
	 *  @param string $user_id Twitter user id.
	 *  @return object $data Twitter user info.
	 */
	public static function user_object( $connection, $user_id ) {

		$data    = new stdClass();
		$content = $connection->get(
			'users/' . $user_id,
			array(
				'user.fields' => 'created_at,description,entities,id,location,name,pinned_tweet_id,profile_image_url,protected,url,username,verified,withheld',
				'expansions'  => 'pinned_tweet_id',
			)
		);

		if ( isset( $content->data ) ) {

			$data->name              = $content->data->name;
			$data->screen_name       = $content->data->username;
			$data->profile_image_url = self::process_image( $content->data->profile_image_url );
			$data->url               = $content->data->url;

		}

		return $data;

	}

	/**
	 *  Process Retweet.
	 *
	 *  @since 1.0.0
	 *
	 *  @param  object $connection Connection.
	 *  @param  array  $tweet Retweeted tweet.
	 *  @param  string $user_name Twitter user name.
	 *  @param  object $xml XML object.
	 *  @return object $xml XML object.
	 */
	public static function process_retweet( $connection, $tweet, $user_name, $xml ) {

		$id   = $tweet->id;
		$text = $tweet->text;
		$date = $tweet->created_at;

		$user = self::user_object( $connection, $tweet->entities->mentions[0]->id );
		$text = self::parse_retweet( $text );
		$text = self::parse_tweet( $text );

		// Retweet original date.
		$retweet_date = self::retweet_date( $connection, $id, $date );

		$twt = $xml->addChild( 'item' );
		$twt->addChild( 'photo', $user->profile_image_url );
		$twt->addChild( 'date', self::process_date( $retweet_date ) );
		self::add_cdata( 'user', $user->screen_name, $twt );
		self::add_cdata( 'name', $user->name, $twt );
		$text = self::add_cdata( 'text', trim( $text ), $twt );
		$text->addAttribute( 'type', $user_name . ' Retweeted' );
		$text->addAttribute( 'id', $id );

		if ( isset( $tweet->attachments ) ) {

			$media_key = $tweet->attachments->media_keys[0];
			$media     = self::tweet_media( $connection, $media_key, $id );

			if ( isset( $tweet->entities->urls ) ) {

				$url = $tweet->entities->urls[0]->url;

			}

			$media = $twt->addChild( 'media', $media[1] );
			$media->addAttribute( 'media_type', $media[0] );
			$media->addAttribute( 'url', $url );

		}

		return $xml;

	}

	/**
	 *  Get a retweets original publishing date.
	 *
	 *  @since 1.0.0
	 *
	 *  @param  object $connection Connection.
	 *  @param  int    $id Tweet ID.
	 *  @param  string $date Date.
	 *  @return string  $date Original publication date.
	 */
	public static function retweet_date( $connection, $id, $date ) {

		$content    = $connection->get( 'tweets/' . $id, array( 'tweet.fields' => 'referenced_tweets' ) );
		$referenced = $content->data->referenced_tweets;

		if ( isset( $referenced ) ) {

			foreach ( $referenced as $key => $tweets ) {

				if ( 'retweeted' === $tweets->type ) {

					$retweet_id = $tweets->id;

				}
			}
		}

		if ( isset( $retweet_id ) ) {

			$content = $connection->get( 'tweets/' . $retweet_id, array( 'tweet.fields' => 'created_at' ) );

			if ( isset( $content->data ) ) {

				$date = $content->data->created_at;

			}
		}

		return $date;

	}

	/**
	 *  Process Twwet media.
	 *
	 *  @since 1.0.0
	 *
	 *  @param  object $connection Connection.
	 *  @param  string $media_key Media key.
	 *  @param  int    $id Tweet ID.
	 *  @return array   $media Media.
	 */
	public static function tweet_media( $connection, $media_key, $id ) {

		$media   = null;
		$content = $connection->get(
			'tweets/' . $id,
			array(
				'expansions'   => 'attachments.media_keys',
				'media.fields' => 'duration_ms,height,media_key,preview_image_url,public_metrics,type,url,width,alt_text',
			)
		);

		if ( isset( $content->includes->media ) ) {

			$tweet_media = $content->includes->media[0];

			if ( $tweet_media->media_key === $media_key ) {

				if ( isset( $tweet_media->url ) ) {

					$media = array( $tweet_media->type, self::process_image( $tweet_media->url ) );

				} else {

					$media = array( $tweet_media->type, self::process_image( $tweet_media->preview_image_url ) );

				}
			}
		}

		return $media;
	}

	/**
	 *  Regex Tweet text.
	 *
	 *  @since 1.0.0
	 *
	 *  @param string $text Tweet text.
	 *  @return string $text Modified text.
	 */
	public static function parse_tweet( $text ) {

		$regex = '|(https?://[^ ]*)|i';
		$text  = preg_replace( $regex, "<a href='$1'>$1</a>", $text );

		$regex = '|#([^ ]*)|i';
		$text  = preg_replace( $regex, "<a href='https://www.twitter.com/hashtag/$1'>#$1</a>", $text );

		$regex = '|@([^ ]*)|i';
		$text  = preg_replace( $regex, "<a href='https://www.twitter.com/$1'>@$1</a>", $text );

		return $text;
	}

	/**
	 *  Regex Retweet text.
	 *
	 *  @since 1.0.0
	 *
	 *  @param string $text Tweet text.
	 *  @return string $text Modified text.
	 */
	public static function parse_retweet( $text ) {

		$regex = '|(RT @[^:]*:)|i';
		$text  = preg_replace( $regex, '', $text );

		$regex = '|(https?://t.co/[a-zA-Z0-9]+$)|i';
		$text  = preg_replace( $regex, '', $text );

		return $text;
	}

	/**
	 *  Import images into WP Media library.
	 *  
	 *  @since 1.0.1 Inclusion of file for when the task is executed asynchronously.
	 *  @since 1.0.0
	 *
	 *  @param string $url Url of Image on Twitter.
	 *  @return string $url Url of image in WP Media library.
	 */
	public static function process_image( $url ) {

		// Gives us access to the download_url() and wp_handle_sideload() functions.
		require_once ABSPATH . 'wp-admin/includes/file.php';

		$timeout_seconds = 5;

		// Download file to temp dir.
		$temp_file = download_url( $url, $timeout_seconds );

		if ( ! is_wp_error( $temp_file ) ) {

			$wp_filetype = wp_check_filetype( $temp_file, null );

			// Array based on $_FILE as seen in PHP file uploads.
			$file = array(
				'name'     => basename( $url ),
				'tmp_name' => $temp_file,
				'error'    => 0,
				'size'     => filesize( $temp_file ),
			);

			$overrides = array(
				// Tells WordPress to not look for the POST form.
				'test_form' => false,               // Setting this to false lets WordPress allow empty files, not recommended.
				'test_size' => true,
			);
			
			if ( ! is_admin() ) {
				require_once( ABSPATH . 'wp-admin/includes/post.php' );
			}
			
			$attachment_id = post_exists( basename( $url ) );

			if ( 0 === $attachment_id ) {

				// Move the temporary file into the uploads directory.
				$results = wp_handle_sideload( $file, $overrides );

				if ( ! empty( $results['error'] ) ) { // phpcs:ignore
					// Insert any error handling here.
				} else {

					$filename = $results['file']; // Full path to the file.
					$url      = $results['url'];  // URL to the file in the uploads dir.
					$type     = $results['type']; // MIME type of the file.

					// Add image as to media library if is not a retweeted profile image.

					$attachment = array(
						'post_mime_type' => $type,
						'post_title'     => basename( $url ),
						'post_content'   => '',
						'post_status'    => 'inherit',
					);

					$attach_id = wp_insert_attachment( $attachment, $filename );

					$imagenew     = get_post( $attach_id );
					$fullsizepath = get_attached_file( $imagenew->ID );
					$attach_data  = wp_generate_attachment_metadata( $attach_id, $fullsizepath );
					wp_update_attachment_metadata( $attach_id, $attach_data );

				}
			} else {
				$url = wp_get_attachment_url( $attachment_id );
			}
		}
		return $url;
	}

	/**
	 *  Process dates.
	 *
	 *  @since 1.0.0
	 *
	 *  @param string $date Date from Twitter feed.
	 *  @return string $date Processed Date.
	 */
	public static function process_date( $date ) {

		$str_date  = strtotime( $date );
		$date_year = gmdate( 'Y', strtotime( $date ) );
		$now_year  = gmdate( 'Y' );
		$one_hour  = 60 * 60;
		$one_day   = 24 * $one_hour;

		if ( $date_year !== $now_year ) {

			$date = gmdate( 'M j,Y', $str_date );

		} else {

			$str_now    = time();
			$difference = $str_now - $str_date;

			if ( $difference < $one_hour ) {

				$date = floor( $difference / 60 ) . 'm';

			} elseif ( $difference >= $one_hour && $difference < $one_day ) {

				$date = floor( $difference / ( 60 * 60 ) ) . 'h';

			} else {

				$date = gmdate( 'M j', $str_date );

			}
		}

		return $date;
	}

	/**
	 * Adds a CDATA property to an XML document.
	 *
	 * @param string $name Name of Node.

	 * @param string $value Value of Node.
	 * @param object $parent Parent Node.
	 * @return object $child child Node.
	 */
	public static function add_cdata( $name, $value, $parent ) {

		$child       = $parent->addChild( $name );
		$child_node  = dom_import_simplexml( $child );
		$child_owner = $child_node->ownerDocument; // phpcs:ignore

		$child_node->appendChild( $child_owner->createCDATASection( $value ) );

		return $child;
	}
}


