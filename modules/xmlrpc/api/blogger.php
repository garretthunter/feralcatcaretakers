<?php
// $Id: blogger.php,v 1.5 2002/11/28 13:16:29 neo Exp $
// ----------------------------------------------------------------------
// POST-NUKE Content Management System
// Copyright (C) 2001 by the Post-Nuke Development Team.
// http://www.postnuke.com/
// ----------------------------------------------------------------------
// Based on:
// PHP-NUKE Web Portal System - http://phpnuke.org/
// Thatware - http://thatware.org/
// ----------------------------------------------------------------------
// LICENSE
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License (GPL)
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// To read the license please visit http://www.gnu.org/copyleft/gpl.html
// ----------------------------------------------------------------------
// Original Author of file: Gregor J. Rothfuss
// Additional fixes: Marcel van der Boom
// Purpose of file: implementation of the blogger xml-rpc api (server)
// ----------------------------------------------------------------------

 
/**
* Delete a posting
* 
* Takes an xmlrpc enveloped message according to blogger api and
* uses it to delete a posting from postnuke articles.
*
* @param  xmlrpcmsg   xml-rpc message with the parameters defined in blogger API
* @return xmlrpcresp  Returns an xmlrpc response message, which contains a true value on success or error on failure
* @see    xmlrpc_userapi_call(), xmlrpcresp, xmlrpcmsg
*/
function xmlrpc_blogger_main_deletePost($m) {
	
	$pntable = pnDBGetTables();
	list($dbconn) = pnDBGetConn();
	$storiescolumn = &$pntable['stories_column'];
	
	// get the params
	// we skip appkey and publish for now..
	$sn1=$m->getParam(1); $postid   = $sn1->scalarval();
	$sn2=$m->getParam(2); $username = $sn2->scalarval();
	$sn3=$m->getParam(3); $password = $sn3->scalarval();
	
	if (!pnUserLogIn($username, $password, false)) {
		$err = "Invalid user ($username) while trying to delete post";
	}	else {
		if (pnSecAuthAction(0, "Stories::Story", "::", ACCESS_DELETE)) { 
			$result = $dbconn->Execute("DELETE FROM $pntable[stories] WHERE $storiescolumn[sid] = '".pnVarPrepForStore($postid)."'");
			
			// if we generated an error, create an error return response
			if ($result === false) {
				PN_DBMsgError($dbconn, __FILE__, __LINE__, "Error accessing the database");
				$err = "DB error";
			}
		} else {
			$err = "User ($username) not authorized to delete post";
		}
	}
	if (!empty($err)) {
		// if we encountered an error, we pass it on
		return new xmlrpcresp(0, $GLOBALS[xmlrpcerruser]+1, $err);
	}	else {
		// otherwise, we create the right response (boolean)
		return new xmlrpcresp(new xmlrpcval(true, "boolean"));
  }
}

$xmlrpc_blogger_main_getPost_sig=array(array($xmlrpcStruct, $xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcString));
/* the params are:
    appkey (string): Unique identifier/passcode of the application sending the post.
    postid (string): Unique identifier of the post that will be fetched.
    username (string): Login for a user who has permission to post to the blog.
    password (string): Password for said username.
*/

$xmlrpc_blogger_main_getPost_doc='Gets the specified story on a Postnuke weblog. Returns
a struct (like the structs in getRecentPosts) containing the
userid, post body, datecreated, and post id. There may be additional
fields returned in the future.

Example request:
http://plant.blogger.com/api/samples/getPostRequest.xml

Example response:
http://plant.blogger.com/api/samples/getPostResponse.xml

User must be a member of the blog. (They do not need to be the one
who made the post or an admin -- as any member of a blog can see
other members posts, though they cannot necessarily edit them.)

And yes, I will get these last couple methods added to documentation
soon.

Note: If you are doing something similar to the blogger.com interface,
where you show a user the most recent posts and they click on one to
edit and then it loads in a form, it would, of course, be better to
cache the data from getRecentPosts() than to subsequently call this.
But this will be handy if you know the post ID and are not calling
getRecentPosts first. For example, it might written out on a
published page.';

/**
* Get a posting
* 
* Takes an xmlrpc enveloped message according to blogger api and
* uses it to return a posting from postnuke articles.
*
* @param  xmlrpcmsg   xml-rpc message with the parameters defined in blogger API
* @return xmlrpcresp  Returns an xmlrpc response message, which contains the 
*                     article on success or errormessage on failure
* @see    xmlrpc_userapi_call(), xmlrpcresp, xmlrpcmsg
*/
function xmlrpc_blogger_main_getPost($m) {
	
	$pntable = pnDBGetTables();
	list($dbconn) = pnDBGetConn();
	$storiescolumn = &$pntable['stories_column'];
	
	// get the params
	// we skip appkey for now..
	$sn1=$m->getParam(1);  $postid   = $sn1->scalarval();
	$sn2=$m->getParam(2);  $username   = $sn2->scalarval();
	$sn3=$m->getParam(3);  $password   = $sn3->scalarval();
	
	//return new xmlrpcresp(0, $xmlrpcerruser+1, "In getPost");
	if (!pnUserLogIn($username, $password, false)) {
		$err = "Invalid user ($username) while getting post";
	}
	else {
		$result = $dbconn->Execute("SELECT $storiescolumn[aid], $storiescolumn[time], $storiescolumn[hometext], "
															 ."$storiescolumn[bodytext], $storiescolumn[sid] FROM $pntable[stories] "
															 ."WHERE $storiescolumn[sid]='".pnVarPrepForStore($postid)."'");
		
		// if we generated an error, create an error return response
		if ($result === false) {
			PN_DBMsgError($dbconn, __FILE__, __LINE__, "Error accesing to the database");
			$err = "DB error";
		} else {
			if ($result->RecordCount()==0) {
				$err ="No posts found";
			}
		}
	}
	if (!empty($err)) {
		// if we encountered an error, we pass it on
		return new xmlrpcresp(0, $GLOBALS[xmlrpcerruser]+1, $err);
	}	else {
		// otherwise, we create the right response
		
		// convert date to iso date code
		$ts = strtotime($result->fields[1]);
		$t = iso8601_encode($ts);
		
		// Content is intro text of article
		$content = $result->fields[2];
		
		// create a struct for the response
		$myStruct=new xmlrpcval(array(
																	"userid" => new xmlrpcval($result->fields[0]),
																	"dateCreated" => new xmlrpcval($t, "dateTime.iso8601"),
																	"content" => new xmlrpcval(htmlspecialchars($content)),
																	"postid" => new xmlrpcval($result->fields[4])),
														"struct");
		
		return new xmlrpcresp($myStruct);
	}
	
}

$xmlrpc_blogger_main_getRecentPosts_sig=array(array($xmlrpcArray, $xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcInt));
/* the params are:
    appkey (string): Unique identifier/passcode of the application sending the post.
    blogid (string): Unique identifier of the blog the posts will be fetched from.
    username (string): Login for a user who has permission to post to the blog.
    password (string): Password for said username.
    numberOfPosts (int): The number of posts to fetch.
*/

$xmlrpc_blogger_main_getRecentPosts_doc='Gets the n most recent stories from a Postnuke weblog. Returns
an array of structs containing the latest n posts to a given
blog, newest first.

Each post struct includes: dateCreated (when post was made), userid
(who made the post), postid, and content.

A request would look something like this:
http://plant.blogger.com/api/samples/getRecentPostsRequest.xml

A successful response would look kinds like this:
http://plant.blogger.com/api/samples/getRecentPostsResponse.xml
Notes:

* numberOfPosts is limited to 20 at this time. Let me know if this
gets annoying. Letting this number get too high could result in some
expensive db access, so I want to be careful with it.

* dateCreated is returned in the timezone of the blog.

* user, of course, must be a member of the blog';


/**
* Get recent postings
* 
* Takes an xmlrpc enveloped message according to blogger api and
* uses it to return the n most recent postings from postnuke articles.
*
* @param  xmlrpcmsg   xml-rpc message with the parameters defined in blogger API
* @return xmlrpcresp  Returns an xmlrpc response message, which contains the 
*                     articles on success or errormessage on failure
* @see    xmlrpc_userapi_call(), xmlrpcresp, xmlrpcmsg
*/
function xmlrpc_blogger_main_getRecentPosts($m) {
	
	$pntable = pnDBGetTables();
	list($dbconn) = pnDBGetConn();
	$storiescolumn = &$pntable['stories_column'];
	
	// get the params
	// we skip appkey for now..
	// we substitute topicid for blogid in pn
	$sn1=$m->getParam(1);  $blogid   = $sn1->scalarval();
	$sn2=$m->getParam(2);  $username   = $sn2->scalarval();
	$sn3=$m->getParam(3);  $password   = $sn3->scalarval();
	$sn4=$m->getParam(4);  $numberOfPosts   = $sn4->scalarval();
	
	if (!pnUserLogIn($username, $password, false)) {
		$err = "Invalid user ($username) in getting recent posts ";
	}	else {
		$uid = xmlrpc_userapi_get_userid($username);
		$sql = "SELECT $storiescolumn[aid], $storiescolumn[time], $storiescolumn[hometext], "
			."$storiescolumn[bodytext], $storiescolumn[sid] FROM $pntable[stories] "
			."WHERE $storiescolumn[aid]='".pnVarPrepForStore($uid)."' AND $storiescolumn[topic]='".pnVarPrepForStore($blogid)."'"
			." ORDER BY $storiescolumn[time] desc";
		
		// little-known implementation detail: set number to 0 to get all posts
		if ($numberOfPosts == 0) {
			$result=$dbconn->Execute($sql);
 		} else {
 			$result=$dbconn->SelectLimit($sql,$numberOfPosts);
 		}
		// if we generated an error, create an error return response
		if ($result === false) {
			PN_DBMsgError($dbconn, __FILE__, __LINE__, "Error accessing to the database");
			$err = "DB error";
		} else {
      if ($result->RecordCount()==0) {
        $err="No blogs found";
      }
    }
	}
	if (!empty($err)) {
		// if we encountered an error, we pass it on
		return new xmlrpcresp(0, $GLOBALS[xmlrpcerruser]+1, $err);
	} else {
		// otherwise, we create the right response
		
		$response = array();
		$i = 0;
		
		for(;!$result->EOF;$result->MoveNext() ) {
			
			// convert date to iso date code
			$ts = strtotime($result->fields[1]);
			$t = iso8601_encode($ts);
			
      // We can only support one text string in the blogger API consistently: intro text
			$content = $result->fields[2];
			
			// create a struct for the response
  		$response[$i]=new xmlrpcval(array(
																			  "userid" => new xmlrpcval($result->fields[0]),
																				"dateCreated" => new xmlrpcval($t, "dateTime.iso8601"),
																	      "content" => new xmlrpcval(htmlspecialchars($content)),
                                        "postid" => new xmlrpcval($result->fields[4])),
                                  "struct");
			$i++;
		}
		$dreck= new xmlrpcval($response, "array");
    return new xmlrpcresp($dreck);
  }
}

$xmlrpc_blogger_main_getUserInfo_sig=array(array($xmlrpcStruct, $xmlrpcString, $xmlrpcString, $xmlrpcString));
/* the params are:
    appkey (string): Unique identifier/passcode of the application sending the post.
    username (string): Login for a user who has permission to post to the blog.
    password (string): Password for said username.
*/

$xmlrpc_blogger_main_getUserInfo_doc='Returns a struct containing users userid, firstname, lastname,
nickname, email, and url.

Example request:
http://plant.blogger.com/api/samples/getUserInfoRequest.xml

Examples response:
http://plant.blogger.com/api/samples/getUserInfoResponse.xml';


/**
* Retrieve info about a user
* 
* Takes an xmlrpc enveloped message according to blogger api and
* uses it to return user info from postnuke
*
* @param  xmlrpcmsg   xml-rpc message with the parameters defined in blogger API
* @return xmlrpcresp  Returns an xmlrpc response message, which contains the 
*                     user info on success or errormessage on failure
* @see    xmlrpc_userapi_call(), xmlrpcresp, xmlrpcmsg
*/
function xmlrpc_blogger_main_getUserInfo($m) {
	
	$pntable = pnDBGetTables();
	list($dbconn) = pnDBGetConn();
	$userscolumn = &$pntable['users_column'];
	
	// get the params
	// we skip appkey for now..
	$sn1=$m->getParam(1);  $username   = $sn1->scalarval();
	$sn2=$m->getParam(2);  $password   = $sn2->scalarval();
	
	if (!pnUserLogIn($username, $password, false)) {
		$err = "Invalid user ($username) while getting user info";
	}	else {
		$result = $dbconn->Execute("SELECT $userscolumn[uname], $userscolumn[uid], $userscolumn[url], "
															 ."$userscolumn[email], $userscolumn[name] FROM $pntable[users] "
															 ."WHERE $userscolumn[uname] ='".pnVarPrepForStore($username)."'");
		
		// if we generated an error, create an error return response
		if ($result === false) {
			PN_DBMsgError($dbconn, __FILE__, __LINE__, "Error accessing the database");
			$err = "DB error";
		} else {
			if($result->RecordCount()==0) {
				$err="No user info found";
			}
		}
	}
	if (!empty($err)) {
		// if we encountered an error, we pass it on
		return new xmlrpcresp(0, $GLOBALS[xmlrpcerruser]+1, $err);
	}	else {
		// otherwise, we create the right response
		
		// create a struct for the response
		$response =new xmlrpcval(array(
																	 "nickname" => new xmlrpcval($result->fields[0]),
																	 "userid" => new xmlrpcval($result->fields[1]),
																	 "url" => new xmlrpcval($result->fields[2]),
																	 "email" => new xmlrpcval($result->fields[3]),
																	 "lastname" => new xmlrpcval($result->fields[4]),
																	 "firstname" => new xmlrpcval("")),
														 "struct");
		
		return new xmlrpcresp($response);
	}
}


$xmlrpc_blogger_main_getUsersBlogs_sig=array(array($xmlrpcArray, $xmlrpcString, $xmlrpcString, $xmlrpcString));
/* the params are:
    appkey (string): Unique identifier/passcode of the application sending the post.
    username (string): Login for a user who has permission to post to the blog.
    password (string): Password for said username.
*/

$xmlrpc_blogger_main_getUsersBlogs_doc='Returns information about all the blogs a
given user is a member of. Data is returned as an array of <struct>s containing
the ID (blogid), name (blogName), and URL (url) of each blog';


/**
* Return topics from postnuke
* 
* Takes an xmlrpc enveloped message according to blogger api and
* uses it to return topics which are the PostNuke equivalent of blogs.
*
* @param  xmlrpcmsg   xml-rpc message with the parameters defined in blogger API
* @return xmlrpcresp  Returns an xmlrpc response message, which contains the 
*                     list of topics on success or errormessage on failure
* @see    xmlrpc_userapi_call(), xmlrpcresp, xmlrpcmsg
* @todo   should we only return topics to which user has access rights?
*/
function xmlrpc_blogger_main_getUsersBlogs($m) {
	
	$pntable = pnDBGetTables();
	list($dbconn) = pnDBGetConn();
	
	// get the params
	// we skip appkey for now..
	$sn1=$m->getParam(1);  $username = $sn1->scalarval();
	$sn2=$m->getParam(2);  $password = $sn2->scalarval();
	
	if (!pnUserLogIn($username, $password, false)) {
		$err = "Invalid user ($username) while getting users blogs";
	} else {
		// FIXME: maybe only return those topics to which user has access rights?
		$storcol = &$pntable['stories_column'];
		$topcol = &$pntable['topics_column'];
		$sql =  "SELECT $topcol[tid] as topicid, $topcol[topictext] as topicname FROM $pntable[topics] ORDER BY $topcol[topictext]";
		$result = $dbconn->Execute($sql);
		
		// if we generated an error, create an error return response
		if ($result === false) {
			PN_DBMsgError($dbconn, __FILE__, __LINE__, "Error accesing to the database");
			$err = "DB error";
		} else {
			if ($result->RecordCount()==0) {
				$err="No Blogs found to which you have access";
			}
		}
	}
	if (!empty($err)) {
		// if we encountered an error, we pass it on
		return new xmlrpcresp(0, $GLOBALS[xmlrpcerruser]+1, $err);
	}	else {
		// otherwise, we create the right response
		
		$response = array();
		$i = 0;
		
		for(;!$result->EOF;$result->MoveNext() ) {
			
			// create a struct for the response
			// FIXME: This will change when News module conforms to pnAPI
			$url = pnGetBaseURL()."modules.php?op=modload&amp;name=News&amp;file=index&amp;topic=".$result->fields[0];
			$response[$i]=new xmlrpcval(array(
																				"url" => new xmlrpcval($url), 
																				"blogid" => new xmlrpcval($result->fields[0]),
																				"blogName" => new xmlrpcval($result->fields[1])
																				),
																	"struct");
			$i++;
		}
		$dreck= new xmlrpcval($response, "array");
		return new xmlrpcresp($dreck);
	}
}


/* NOTE The first parameter of the signature is the return value
 */
$xmlrpc_blogger_main_newPost_sig=array(array($xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcBoolean));

/* the params are:
    appkey (string): Unique identifier/passcode of the application sending the post.
    blogid (string): Unique identifier of the blog the post will be added to.
    username (string): Login for a user who has permission to post to the blog.
    password (string): Password for said username.
    content (string): Contents of the post.
    publish (boolean): If true, the blog will be published immediately after the post is made.
*/


$xmlrpc_blogger_main_newPost_doc='Posts a story to a Postnuke weblog.
Optionally, will publish the blog after making the post.
On success, it returns the unique ID of the new post.
On error, it will return some error message.';

/**
* Create a new posting
* 
* Takes an xmlrpc enveloped message according to blogger api and
* uses it to create a new posting in postnuke articles.
*
* @param  xmlrpcmsg   xml-rpc message with the parameters defined in blogger API
* @return xmlrpcresp  Returns an xmlrpc response message, which contains the 
*                     article-id on success or errormessage on failure
* @see    xmlrpc_userapi_call(), xmlrpcresp, xmlrpcmsg
*/
function xmlrpc_blogger_main_newPost($m) {
	
	
	list($dbconn) = pnDBGetConn();
	$pntable = pnDBGetTables();
	$storiescolumns = &$pntable['stories_column'];
	
	setlocale(LC_TIME, pnConfigGetVar('locale'));
	// get the params
	// we skip appkey
	$sn1=$m->getParam(1);  $topic   = $sn1->scalarval();
	$sn2=$m->getParam(2);  $username   = $sn2->scalarval();
	$sn3=$m->getParam(3);  $password   = $sn3->scalarval();
	$sn4=$m->getParam(4);  $content   = $sn4->scalarval();
	$sn5=$m->getParam(5);  $publish   = $sn5->scalarval();

	
	if (!pnUserLogIn($username, $password, false)) {
		$err = "Invalid user ($username) while creating new post";
	}
	else {
		if (pnSecAuthAction(0, "Stories::Story", "::", ACCESS_ADD)) {
			$bodytext = "";
	      	$separator = "<!--Blog Title-->";
			$title = "*";
			$subject = ml_ftime(_DATETIMEBRIEF, GetUserTime(time())) . $postid;
			// $content contains the use edittable part, so do some sanity checks
			// Fixes that allow w.bloggar to work with xmlrpc correctly - [Neo]
			$content = str_replace("&lt;title&gt;","<!--Blog Title-->",$content);
			$content = str_replace("&lt;/title&gt;","<!--Blog Title-->",$content);
			$content = str_replace("<title>","<!--Blog Title-->",$content);
			$content = str_replace("</title>","<!--Blog Title-->",$content);
			$content = explode ( $separator, $content );
			$title   = $title . $content[1];
			$hometext = pnVarPrepHTMLDisplay($content[2]);
			$uid = xmlrpc_userapi_get_userid($username);
			
			$sql = "INSERT INTO $pntable[stories] ($storiescolumns[sid], $storiescolumns[aid], $storiescolumns[catid], "
				."$storiescolumns[title], $storiescolumns[time], $storiescolumns[hometext], "
				."$storiescolumns[bodytext], $storiescolumns[comments], $storiescolumns[counter],  "
				."$storiescolumns[ihome], $storiescolumns[topic], $storiescolumns[informant]) VALUES (NULL, '".pnVarPrepForStore($uid)."', '0',"
				."'".pnVarPrepForStore($title)."', now(), '".pnVarPrepForStore($hometext)."', '".pnVarPrepForStore($bodytext)."', "
				."'0', '0', '".pnVarPrepForStore($publish)."','".pnVarPrepForStore($topic)."', '".pnVarPrepForStore($username)."')";
			
			$result = $dbconn->Execute($sql);
			
			// if we generated an error, create an error return response
			if ($result === false) {
				PN_DBMsgError($dbconn, __FILE__, __LINE__, "Error accessing the database");
				$err = "DB error";
			}
		} else {
			$err = "User ($username) not authorized to create a new post";
		}      
	}
	if (!empty($err)) {
		// if we encountered an error, we pass it on
		return new xmlrpcresp(0, $GLOBALS[xmlrpcerruser]+1, $err);
	}	else {
		// otherwise, we create the right response
		// with the id of the new post
		return new xmlrpcresp(new xmlrpcval($dbconn->PO_Insert_ID('stories','pn_sid')));
  }
} // newPost

$xmlrpc_blogger_main_editPost_sig=array(array($xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcBoolean));
/* the params are:
    appkey (string): Unique identifier/passcode of the application sending the post.
    postid (string): Unique identifier of the post to be changed.
    username (string): Login for a user who has permission to post to the blog.
    password (string): Password for said username.
    content (string): Contents of the post.
    publish (boolean): If true, the blog will be published immediately after the post is made.
*/


$xmlrpc_blogger_main_editPost_doc='Edits a story on a Postnuke weblog.
Optionally, will publish the blog the post belongs
to after changing the post. On success, it returns a boolean true value.
On error, it will return a fault with an error message';

/**
* Edit a posting
* 
* Takes an xmlrpc enveloped message according to blogger api and
* uses it to modify a posting from postnuke articles.
*
* @param  xmlrpcmsg   xml-rpc message with the parameters defined in blogger API
* @return xmlrpcresp  Returns an xmlrpc response message, which contains true 
*                     on success or errormessage on failure
* @see    xmlrpc_userapi_call(), xmlrpcresp, xmlrpcmsg
*/
function xmlrpc_blogger_main_editPost($m) {
	
	$pntable = pnDBGetTables();
	list($dbconn) = pnDBGetConn();
	$storiescolumn = $pntable['stories_column'];
  
	//return new xmlrpcresp(0, $xmlrpcerruser+1, "In editPost");
	// get the params
	// we skip appkey
	$sn1=$m->getParam(1);  $postid   = $sn1->scalarval();
	$sn2=$m->getParam(2);  $username   = $sn2->scalarval();
	$sn3=$m->getParam(3);  $password   = $sn3->scalarval();
	$sn4=$m->getParam(4);  $content   = $sn4->scalarval();
	$sn5=$m->getParam(5);  $publish   = $sn5->scalarval();
	
	if (!pnUserLogIn($username, $password, false)) {
		$err = "Invalid user ($username) while editting post";
	} else {
		if (pnSecAuthAction(0, "Stories::Story", "::", ACCESS_EDIT)) {

	      	$separator = "<!--Blog Title-->";
			// $content contains the use edittable part, so do some sanity checks
			// Fixes that allow w.bloggar to work with xmlrpc correctly - [Neo]
			$content = str_replace("&lt;title&gt;","<!--Blog Title-->",$content);
			$content = str_replace("&lt;/title&gt;","<!--Blog Title-->",$content);
			$content = str_replace("<title>","<!--Blog Title-->",$content);
			$content = str_replace("</title>","<!--Blog Title-->",$content);
			$content = explode ( $separator, $content );
			$title   = $title . $content[1];

			$hometext = pnVarPrepHTMLDisplay($content[2]);
			
			$sql= "UPDATE $pntable[stories] SET $storiescolumn[hometext]='".pnVarPrepForStore($hometext)."', "
				."$storiescolumn[ihome]='".pnVarPrepForStore($publish)."' "
				."WHERE $storiescolumn[sid]='".pnVarPrepForStore($postid)."'";
			$result = $dbconn->Execute($sql);
			
			// if we generated an error, create an error return response
			if ($result === false) {
				//_DBMsgError($dbconn, __FILE__, __LINE__, "Error accesing to the database");
				$err = "DB error";
			}
		} else {
			$err = "User ($username) not authorized to edit post";
		}
	}
	if (!empty($err)) {
		// if we encountered an error, we pass it on
		return new xmlrpcresp(0, $GLOBALS[xmlrpcerruser]+1, $err);
	}	else {
		// otherwise, we create the right response (boolean)
		return new xmlrpcresp(new xmlrpcval(true, "boolean"));
  }
}

$xmlrpc_blogger_main_deletePost_sig=array(array($xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcBoolean));
/* the params are:
    appkey (string): Unique identifier/passcode of the application sending the post.
    postid (string): Unique identifier of the post to be changed.
    username (string): Login for a Blogger user who has permission to post to the blog.
    password (string): Password for said username.
    publish (boolean): If true, the blog will be published immediately after the post is made.
*/

$xmlrpc_blogger_main_deletePost_doc='Deletes a story on a Postnuke weblog. Returns .';

$_xmlrpc_blogger_dmap=array(
														"blogger.newPost" =>	 array("function" => "xmlrpc_blogger_main_newPost",
																												 "signature" => $xmlrpc_blogger_main_newPost_sig,
																												 "docstring" => $xmlrpc_blogger_main_newPost_doc),
														
														"blogger.editPost" => array("function" => "xmlrpc_blogger_main_editPost",
																												"signature" => $xmlrpc_blogger_main_editPost_sig,
																												"docstring" => $xmlrpc_blogger_main_editPost_doc),
														
														"blogger.deletePost" => array("function" => "xmlrpc_blogger_main_deletePost",
																													"signature" => $xmlrpc_blogger_main_deletePost_sig,
																													"docstring" => $xmlrpc_blogger_main_deletePost_doc),
														
														"blogger.getPost" =>	 array("function" => "xmlrpc_blogger_main_getPost",
																												 "signature" => $xmlrpc_blogger_main_getPost_sig,
																												 "docstring" => $xmlrpc_blogger_main_getPost_doc),
														
														"blogger.getRecentPosts" =>	array("function" => "xmlrpc_blogger_main_getRecentPosts",
																															"signature" => $xmlrpc_blogger_main_getRecentPosts_sig,
																															"docstring" => $xmlrpc_blogger_main_getRecentPosts_doc),
														
														"blogger.getUserInfo" =>	array("function" => "xmlrpc_blogger_main_getUserInfo",
																														"signature" => $xmlrpc_blogger_main_getUserInfo_sig,
																														"docstring" => $xmlrpc_blogger_main_getUserInfo_doc),
														
														"blogger.getUsersBlogs" =>	array("function" => "xmlrpc_blogger_main_getUsersBlogs",
																															"signature" => $xmlrpc_blogger_main_getUsersBlogs_sig,
																															"docstring" => $xmlrpc_blogger_main_getUsersBlogs_doc));

?>