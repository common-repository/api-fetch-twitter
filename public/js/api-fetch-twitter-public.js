onload = (() => {

    if (document.getElementById('api-fetch-twitter')) {

        let xml_feed = new URL(API_FETCH_TWITTER.feed);

        fetch(xml_feed).then(
            function(response) {
                if (response.ok) {
                    response.text()
                        .then(
                            data => {
                                const parser = new DOMParser();
                                const xml = parser.parseFromString(data, "application/xml");
                                display_tweets(xml);
                            }
                        ).catch(
                            (error) => {
                                console.log;
                            }
                        );
                } else {
                    if (404 === response.status) {
                        var html = '<div style="text-align:center;margin:10px;">';
                        html += 'Import your first Twitter feed';
                        html += '</div>';
                        document.getElementById('api-fetch-twitter').innerHTML = html;
                    }
                }
            }
        );
		
        /**
         *  Parse XMl feed and display Tweets.
         *  
         *  @param object {the_request} XML object
         */
        function display_tweets(the_request) {

            var html = '';
            var tweet_content = getItems(the_request, 'data');

			for (var loop = 0; loop < tweet_content.length; loop++) {
				console.log(loop);
                var photo = getFirstValue(tweet_content[loop], 'photo');
                var date = getFirstValue(tweet_content[loop], 'date');
                var name = getFirstValue(tweet_content[loop], 'name');
                var user = getFirstValue(tweet_content[loop], 'user');
                var text = getFirstValue(tweet_content[loop], 'text');

                text = text.replace(/\n/g, "<br/>");

                var id = getFirstAttribute(tweet_content[loop], 'text', 'id');
                var media = getFirstValue(tweet_content[loop], 'media');
                var media_url = getFirstAttribute(tweet_content[loop], 'media', 'url');
                var type = getFirstAttribute(tweet_content[loop], 'text', 'type');

                html += "<div class='tweet'>";
                html += "<a href='https://twitter.com/" + user + "' target='_blank'>";

                if ('Tweet' !== type) {
                    html += "<div class='retweeted-tweet'><img src='" + API_FETCH_TWITTER.image_dir + "retweet_hover.png' class='user-retweet'/> " + type + "</div>";
                }

                html += "<img alt='Twitter profile image' src='" + photo + "' class='user-profile-image'>";
                html += name;
                html += "<br><span class='user-link'>@" + user + "</span>";
                html += "&nbsp;&middot;&nbsp;<span>" + date + "</span></a>";

                html += "<div class='tweet-text'>" + text + "</div>";

                if (null !== media) {
                    html += "<a href='" + media_url + "' target='_blank'>";
                    html += "<img src='" + media + "' class='tweet-media'/>";
                    html += "</a>";
                }

                html += "<div class='tweet-action'>";
                html += "<a href='https://twitter.com/intent/tweet?in_reply_to=" + id + "' target='_blank' class='reply'>";
                html += "<img src='" + API_FETCH_TWITTER.image_dir + "reply.png' alt='reply'>reply</a>";
                html += "<a href='https://twitter.com/intent/retweet?tweet_id=" + id + "' target='_blank' class='retweet'>";
                html += "<img src='" + API_FETCH_TWITTER.image_dir + "retweet.png' alt='retweet'>retweet</a>";
                html += "<a href='https://twitter.com/intent/favorite?tweet_id=" + id + "' target='_blank' class='favorite'>";
                html += "<img src='" + API_FETCH_TWITTER.image_dir + "favorite.png' alt='favorite'>favorite</a>";
                html += "</div>";

                html += "</div>";

            }
			
            document.getElementById('api-fetch-twitter').innerHTML = html;

            var tweets = new Array("reply", "retweet", "favorite");

            for (var i = 0; i < tweets.length; i++) {

                document.querySelectorAll("#api-fetch-twitter ." + tweets[i]).forEach(
                    i => {
                        i.addEventListener(
                            "mouseover",
                            e => {
                                e.currentTarget.firstChild.src = API_FETCH_TWITTER.image_dir + e.currentTarget.firstChild.alt + '_hover.png';
                            }
                        );
                        i.addEventListener(
                            "mouseout",
                            e => {
                                e.currentTarget.firstChild.src = API_FETCH_TWITTER.image_dir + e.currentTarget.firstChild.alt + '.png';
                            }
                        );
                        i.addEventListener(
                            "mousedown",
                            e => {
                                e.currentTarget.firstChild.src = API_FETCH_TWITTER.image_dir + e.currentTarget.firstChild.alt + '_on.png';
                            }
                        );
                    }
                );

            }
        }
	
        /**
         *  Get XML Items.
         *  
         *  @param object {xml_info} XML Object
         *  @param string {item_type} String detailing which items to return.
         *  @return array {the_items_array} Items array.
         */
        function getItems(xml_info, item_type) {
            var the_items_array = new Array();
            var items_element = xml_info.getElementsByTagName(item_type)[0];
            var items = items_element.getElementsByTagName("item");
            for (var loop = 0; loop < items.length; loop++) {
                the_items_array[loop] = items[loop];
            }
            return the_items_array;
        }
		
        /**
         *  Get XML Node Value.
         *  
         *  @param object {my_element} XML Node.
         *  @param string {child} XML Node name.
         *  @return string Node value.
         */
        function getFirstValue(my_element, child) {
            if (undefined === my_element.getElementsByTagName(child)[0]) {
                return null;
            }
            return my_element.getElementsByTagName(child)[0].firstChild.nodeValue;
        }
		
        /**
         *  Get XML Attribute
         *  
         *  @param object {my_element} XML Node.
	     *  @param string {child} XML Node name.
         *  @param string {attribute} Attribute name.
         *  @return string Attribute value.
         */
        function getFirstAttribute(my_element, child, attribute) {
            if (undefined === my_element.getElementsByTagName(child)[0]) {
                return null;
            }
            return my_element.getElementsByTagName(child)[0].getAttribute(attribute);
        }

    }

});

onload();