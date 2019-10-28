<?php
	/*
		Class: BigTree\CSRF
			Handles cross site request forgery prevention measures.
	*/
	
	namespace BigTree;
	
	class CSRF {

		public static $Field;
		public static $Token;

		private static function checkSetup(): void
		{
			if (empty(static::$Field) || empty(static::$Token)) {
				trigger_error(Text::translate("You must call CSRF::generate or CSRF::setup before any other CSRF method."), E_USER_ERROR);
			}
		}

		/*
			Function: drawPOSTToken
				Draws an input field for the CSRF token.
		*/
		
		public static function drawPOSTToken(): void
		{
			static::checkSetup();

			echo '<input type="hidden" value="'.htmlspecialchars(static::$Token).'" name="'.static::$Field.'" />';
		}

		/*
			Function: drawGETToken
				Draws a GET variable in a URL for the CSRF token.
		*/
		
		public static function drawGETToken(): void
		{
			static::checkSetup();

			echo '&'.static::$Field.'='.urlencode(static::$Token);
		}

		/*
			Function: generate
				Generates a random field and token value and sets them.
		*/

		public static function generate(): void
		{
			static::$Token = base64_encode(openssl_random_pseudo_bytes(32));
			static::$Field = "__csrf_token_".Text::getRandomString(32)."__";
		}

		/*
			Function: setup
				Sets up the environment for the proper token and field.

			Parameters:
				field - The expected GET/POST field
				token - The expected token to be passed
		*/

		public static function setup(string $field, string $token): void
		{
			static::$Field = $field;
			static::$Token = $token;
		}

		/*
			Function: verify
				Verifies the referring host and session token and stops processing if they fail.
		
			Parameters:
				api_call - If set to true, will return a failed API call response on failure
		*/
		
		public static function verify(bool $api_call = false): void
		{
			static::checkSetup();

			$clean_referer = str_replace(["http://", "https://"], "//", $_SERVER["HTTP_REFERER"]);
			$clean_domain = str_replace(["http://", "https://"], "//", DOMAIN);
			$token = isset($_POST[static::$Field]) ? $_POST[static::$Field] : $_GET[static::$Field];
			
			if (strpos($clean_referer, $clean_domain) !== 0 || $token != static::$Token) {
				
				if ($api_call) {
					API::triggerError("Cross site request forgery detected.", "csrf:error");
				} else {
					Auth::stop(Text::translate("An error has occurred. Please try your submission again."));
				}
			}
		}

	}
