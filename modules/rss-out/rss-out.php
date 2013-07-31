<?php

/**
 * Module to output RSS feeds.
 */

class PF_RSS_Out extends PF_Module {

	/////////////////////////////
	// PARENT OVERRIDE METHODS //
	/////////////////////////////

	/**
	 * Constructor
	 */
	public function __construct() {
		global $pf;

		parent::start();
		add_action('init', array($this, 'request_feed'));
		//self::check_nonce = wp_create_nonce('retrieve-pressforward');
		
		
	}
	
	function module_setup(){
		$mod_settings = array(
			'name' => 'RSS Output Module',
			'slug' => 'rss-out',
			'description' => 'This module provides a way to output RSS Feeds from your subscribed items.',
			'thumbnail' => '',
			'options' => ''
		);
		
		update_option( PF_SLUG . '_' . $this->id . '_settings', $mod_settings );	

		//return $test;
	}

	function request_feed(){
		# global $wp_rewrite;
		add_feed('feedforward', array($this, 'all_feed_assembler'));		
		# Called because stated requirement at http://codex.wordpress.org/Rewrite_API/add_feed
		# Called as per http://codex.wordpress.org/Rewrite_API/flush_rules
		# $wp_rewrite->flush_rules();		

	}

	function all_feed_assembler(){
		
		if (is_set($_POST['from'])){ $fromUT = $_POST['from']; } else {$fromUT = 0;}
		if (is_set($_POST['limitless']) && $_POST['limitless'] == 'true'){ $fromUT = true; } else {$fromUT = false;}
		if ($fromUT < date('U')) {$fromUT = false;}
		header('Content-Type: application/rss+xml; charset='.get_option('blog_charset'), true);
		echo '<?xml version="1.0"?>';
		echo "<!-- RSS Generated by PressForward plugin on " . get_site_url() . " on " . date('m/d/Y; h:i:s A T') . " -->\n";
		?><rss version="2.0" xmlns:blogChannel="http://backend.userland.com/blogChannelModule" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:wfw="http://wellformedweb.org/CommentAPI/" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:sy="http://purl.org/rss/1.0/modules/syndication/" xmlns:slash="http://purl.org/rss/1.0/modules/slash/" xmlns:freebase="http://rdf.freebase.com/ns/internet/website_category">
			<channel>
				<title><?php bloginfo('name'); ?> - PressForward Unfiltered Feed</title>
				<link><?php echo home_url('/?feed=feedforward'); ?></link>
				<description>The aggregation of all feeds collected with PressForward at <?php bloginfo('name'); ?></description>
				<language><?php bloginfo('language'); ?></language>
				<?php 
				#<blogChannel:blogRoll></blogChannel:blogRoll> 
				#<blogChannel:mySubscriptions></blogChannel:mySubscriptions>
				?>
				<blogChannel:blink>http://pressforward.org/news/</blogChannel:blink>
				<copyright>CC0</copyright>
				<lastBuildDate><?php echo date('D, d M Y H:i:s O'); ?></lastBuildDate>
				<atom:link href="<?php echo home_url('/?feed=feedforward'); ?>" rel="self" type="application/rss+xml" />
				<docs>http://feed2.w3.org/docs/rss2.html</docs>
				<generator>PressForward</generator>
				<!-- Built based on MQL spec (http://wiki.freebase.com/wiki/MQL) for queries in style of [{  "type": "/internet/website_category", "id": null, "name": "Aggregator" }] -->
				<category domain="Freebase" title="name">Aggregator</category>
				<category domain="Freebase" title="mid">/m/075x5v</category>
				<category domain="Freebase" title="id">/en/aggregator</category>
				<freebase:name>Aggregator</freebase:name>
				<freebase:mid>/m/075x5v</freebase:mid>
				<freebase:id>/en/aggregator</freebase:id>
				<?php $userObj = get_user_by('email',get_bloginfo('admin_email')); ?>
				<managingEditor><?php bloginfo('admin_email'); ?> (<?php echo $userObj->display_name; ?>)</managingEditor>
				<webMaster><?php bloginfo('admin_email'); ?> (<?php echo $userObj->display_name; ?>)</webMaster>
				<ttl>30</ttl>
				<?php
					$c = 0;
					foreach(PF_Feed_Item::archive_feed_to_display(0, 50, $fromUT, $limitless) as $item) {
						echo '<item>';
							?>
							<title><![CDATA[<?php echo $item['item_title']; ?>]]></title>
							<?php
							# <link> should send users to published nominations when available.
							?>						
							<link><?php echo $item['item_link']; ?></link>
							<guid><?php echo $item['item_link']; ?></guid>
							<?php
							if (!empty($item['item_tags'])){
								$items = explode(',', $item['item_tags']);
								if (!empty($items)){
									foreach ($items as $tag){
										echo '<category><![CDATA['.$tag.']]></category>';
									}
								}
							}
							?>
							<dc:creator><?php echo $item['item_author']; ?></dc:creator>
							<description><![CDATA[<?php echo pf_feed_excerpt($item['item_content']); ?>]]></description>
							<content:encoded><![CDATA[<?php echo $item['item_content']; ?>]]></content:encoded>
							<pubDate><?php echo date( 'D, d M Y H:i:s O' , strtotime($item['item_date'])); ?></pubDate>
							
							<?php
							# Should use <source>, but not passing along RSS link, something to change.
							# <guid></guid>
						echo '</item>';
						if ($c++ == 50) break;
					}
				?>
			</channel>
		</rss>
	<?php
	}

}
