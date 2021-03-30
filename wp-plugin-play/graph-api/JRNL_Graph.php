<?php

class JRNL_Graph {
	var $data = array( "nodes"=>array(), "links"=>array() );
	function __construct() {
	}

	function populateEverything() {
		$this->erase();
		$this->populatePosts();
		$this->populateComments();
		$this->populateTerms();
		$this->populateTermRels();
		$this->populateUsers();
	}

	function erase() {
		$this->data["nodes"] = array();
		$this->data["links"] = array();
	}

	function populatePosts() {
		global $wpdb;	
		$post_filter = $wpdb->posts.".post_type IN ('page','post','attachment') AND $wpdb->posts.post_status = 'publish'";
		$posts = $wpdb->get_results("SELECT ID,post_author,post_date,post_date_gmt,post_content,post_title,post_excerpt,post_status,post_name,post_modified,post_modified_gmt,post_content_filtered,guid,post_type,post_mime_type,comment_count FROM $wpdb->posts WHERE $post_filter" );
		foreach( $posts as $post ) {
			$this->addPost( $post );
		}
	}

	function addPost( $post ) {
		$post_id = "post/".$post->ID;
		$user_id = "user/".$post->post_author;
		$this->data["nodes"][$post_id] = array( 
		              "id" => $post_id,
		            "type" => $post->post_type,
		           "title" => $post->post_title,
			    "data" => array(
			            "html" => $post->post_content,
			            "date" => $post->post_date,
			        "modified" => $post->post_modified,

			            "guid" => $post->guid,
			        "date_gmt" => $post->post_date_gmt,
			         "excerpt" => $post->post_excerpt,
			            "name" => $post->post_name,
			    "modified_gmt" => $post->post_modified_gmt,
			"content_filtered" => $post->post_content_filtered,
			       "mime_type" => $post->post_mime_type,
	//		   "comment_count" => $post->post_comment_count, DANNY
			),
		);
		$this->data["links"][]= array( 
			   "type" => "creator",
			"subject" => $post_id,
			 "object" => $user_id
		);
	}
		
	function populateComments() {
		global $wpdb;	
		$comment_filter = $wpdb->comments.".comment_approved=1";
		$comments = $wpdb->get_results("SELECT comment_ID, comment_post_ID, comment_author, comment_author_url, comment_date, comment_date_gmt, comment_content, comment_parent, user_id FROM $wpdb->comments WHERE $comment_filter",OBJECT);
		foreach( $comments as $comment ) {
			$this->addComment( $comment );
		}
	} 

	function addComment( $comment ) {
		$comment_id = "comment/".$comment->comment_ID;
		$this->data["nodes"][$comment_id] = array( 
			              "id" => $comment_id,
			            "type" => "comment",
				   "title" => $this->trimLength( $comment->comment_author, 16 ).": ".$this->trimLength( strip_tags($comment->comment_content),50),
				    "data" => array(
			            	"html" => $comment->comment_content,
			            	"date" => $comment->comment_date,
			    	"creator_name" => $comment->comment_author,
			     	 "creator_url" => $comment->comment_author_url,
			            "date_gmt" => $comment->comment_date_gmt,
				   )
		);

		$post_id = "post/".$comment->comment_post_ID;
		$this->data["links"][]= array( 
			   "type" => "comments",
			"subject" => $comment_id,
			 "object" => $post_id
		);
		if( $comment->user_id ) {
			$user_id = "user/".$comment->user_id;
			$this->data["links"][]= array( 
			   	"type" => "creator",
				"subject" => $comment_id,
			 	"object" => $user_id
			);
		}
		if( $comment->comment_parent ) {
			$parent_id = "comment/".$comment->comment_parent;
			$this->data["links"][]= array( 
			   	"type" => "comments",
				"subject" => $comment_id,
			 	"object" => $user_id
			);
		}
	}

	function populateTerms() {
		global $wpdb;	
		$terms_filter = $wpdb->term_taxonomy.".taxonomy IN ('category','post_tag')";
		$terms = $wpdb->get_results("SELECT $wpdb->term_taxonomy.term_taxonomy_id, $wpdb->terms.name, $wpdb->terms.slug, $wpdb->term_taxonomy.taxonomy, $wpdb->term_taxonomy.description, $wpdb->term_taxonomy.parent, $wpdb->term_taxonomy.count FROM $wpdb->terms INNER JOIN $wpdb->term_taxonomy ON ( $wpdb->terms.term_id = $wpdb->term_taxonomy.term_id) WHERE $terms_filter",OBJECT);
		foreach( $terms as $term ) {
			$this->addTerm( $term );
		}
	}

	function addTerm( $term ) {
		$term_id = "term/".$term->term_taxonomy_id;
		$this->data["nodes"][$term_id] = array( 
		              "id" => $term_id,
		            "type" => $term->taxonomy,
		           "title" => $term->name,
 			    "data" => array( 
			     "description" => $term->description,
			            "slug" => $term->slug,
			           "count" => $term->count
			)
		);

		if( $term->parent ) {
			$parent_id = "term/".$term->parent;
			$this->data["links"][]= array( 
			   	"type" => "broader",
				"subject" => $term_id, # nb. I'm muddling taxonomy term & term_id
			 	"object" => $parent_id
			);
		}
	}

	function populateTermRels() {
		global $wpdb;	
		$termrel_filter = "1=1";
		$termrels = $wpdb->get_results("SELECT object_id, term_taxonomy_id FROM $wpdb->term_relationships WHERE $termrel_filter",OBJECT);
		foreach( $termrels as $termrel ) {
			$this->addTermRel( $termrel );
		}
	}

	function addTermRel( $termrel ) {
		$post_id = "post/".$termrel->object_id; # this makes me suspicious are we *sure* it's always a post? what are these links thingies?
		$term_id = "term/".$termrel->term_taxonomy_id;
		$this->data["links"][]= array( 
		   	"type" => "subject", # dcterms:subject
			"subject" => $post_id,
		 	"object" => $term_id
		);
	}

	function populateUsers() {
		global $wpdb;	
		$user_filter = "TRUE";
		$users = $wpdb->get_results("SELECT ID, user_email, user_url, display_name FROM $wpdb->users WHERE $user_filter",OBJECT);
		foreach( $users as $user ) {
			$this->addUser( $user );
		}
	}

	function addUser( $user ) {
		$user_id = "user/".$user->ID;
		# we have to be careful not to expose anything about users that isn't world visible already 
		$node = array( 
			"type" => "user",
			  "id" => $user_id,
		       "title" => $user->display_name,
			"data" => array()
		);
		// if( $user->url ) {
		//	$node["data"]["page"] = $user->user_url;
		// }
		if( $user->user_email ) {
			$node["data"]["icon"] = "http://0.gravatar.com/avatar/".md5($user->user_email);
		}
		$this->data["nodes"][$user_id] = $node;
	}

	function asData() {
		return $this->data;
	}
		
	function asJSON() {
		return json_encode( $this->data );
	}

	function trimLength($str, $length) {
		if( strlen($str)<=$length ) {
			return $str;
		}
		$elipsis = "...";
		return substr( $str,0,$length-strlen($elipsis)).$elipsis;
	}
}


