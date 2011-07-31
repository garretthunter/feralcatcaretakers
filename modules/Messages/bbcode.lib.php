<?php
// File: $Id: bbcode.lib.php,v 1.3 2002/10/02 16:04:40 larsneo Exp $
/**
 * pn_bbdecode/pn_bbencode functions:
 * Rewritten - Nathan Codding - Aug 24, 2000
 * Using Perl-Compatible regexps now. Won't kill special chars
 * outside of a [code]...[/code] block now, and all BBCode tags
 * are implemented.
 * Note: the "i" matching switch is used, so BBCode tags are
 * case-insensitive.
 */
function pn_bbdecode($message) {

		// Undo [code]
		$message = preg_replace("#<!-- BBCode Start --><TABLE BORDER=0 ALIGN=CENTER WIDTH=85%><TR><TD>Code:<HR></TD></TR><TR><TD><PRE>(.*?)</PRE></TD></TR><TR><TD><HR></TD></TR></TABLE><!-- BBCode End -->#s", "[code]\\1[/code]", $message);

		// Undo [quote]
		$message = preg_replace("#<!-- BBCode Quote Start --><TABLE BORDER=0 ALIGN=CENTER WIDTH=85%><TR><TD>Quote:<HR></TD></TR><TR><TD><BLOCKQUOTE>(.*?)</BLOCKQUOTE></TD></TR><TR><TD><HR></TD></TR></TABLE><!-- BBCode Quote End -->#s", "[quote]\\1[/quote]", $message);

		// Undo [b] and [i]
		$message = preg_replace("#<!-- BBCode Start --><B>(.*?)</B><!-- BBCode End -->#s", "[b]\\1[/b]", $message);
		$message = preg_replace("#<!-- BBCode Start --><I>(.*?)</I><!-- BBCode End -->#s", "[i]\\1[/i]", $message);

		// Undo [url] (both forms)
		$message = preg_replace("#<!-- BBCode Start --><A HREF=\"http://(.*?)\" TARGET=\"_blank\">(.*?)</A><!-- BBCode End -->#s", "[url=\\1]\\2[/url]", $message);

		// Undo [email]
		$message = preg_replace("#<!-- BBCode Start --><A HREF=\"mailto:(.*?)\">(.*?)</A><!-- BBCode End -->#s", "[email]\\1[/email]", $message);

		// Undo [img]
		$message = preg_replace("#<!-- BBCode Start --><IMG SRC=\"http://(.*?)\"><!-- BBCode End -->#s", "[img]http://\\1[/img]", $message);
		//$message = preg_replace("#<!-- BBCode Start --><IMG SRC=\"(.*?)\"><!-- BBCode End -->#s", "[img]\\1[/img]", $message);

		// Undo lists (unordered/ordered)

		// unordered list code..
		$matchCount = preg_match_all("#<!-- BBCode ulist Start --><UL>(.*?)</UL><!-- BBCode ulist End -->#s", $message, $matches);

		for ($i = 0; $i < $matchCount; $i++)
		{
			$currMatchTextBefore = preg_quote($matches[1][$i]);
			$currMatchTextAfter = preg_replace("#<LI>#s", "[*]", $matches[1][$i]);

			$message = preg_replace("#<!-- BBCode ulist Start --><UL>$currMatchTextBefore</UL><!-- BBCode ulist End -->#s", "[list]" . $currMatchTextAfter . "[/list]", $message);
		}

		// ordered list code..
		$matchCount = preg_match_all("#<!-- BBCode olist Start --><OL TYPE=([A1])>(.*?)</OL><!-- BBCode olist End -->#si", $message, $matches);

		for ($i = 0; $i < $matchCount; $i++)
		{
			$currMatchTextBefore = preg_quote($matches[2][$i]);
			$currMatchTextAfter = preg_replace("#<LI>#s", "[*]", $matches[2][$i]);

			$message = preg_replace("#<!-- BBCode olist Start --><OL TYPE=([A1])>$currMatchTextBefore</OL><!-- BBCode olist End -->#si", "[list=\\1]" . $currMatchTextAfter . "[/list]", $message);
		}

		return($message);
}

function pn_bbencode($message) {

	// [CODE] and [/CODE] for posting code (HTML, PHP, C etc etc) in your posts.
	$matchCount = preg_match_all("#\[code\](.*?)\[/code\]#si", $message, $matches);

	for ($i = 0; $i < $matchCount; $i++)
	{
		$currMatchTextBefore = preg_quote($matches[1][$i]);
		$currMatchTextAfter = htmlspecialchars($matches[1][$i]);
		$message = preg_replace("#\[code\]$currMatchTextBefore\[/code\]#si", "<!-- BBCode Start --><TABLE BORDER=0 ALIGN=CENTER WIDTH=85%><TR><TD>Code:<HR></TD></TR><TR><TD><PRE>$currMatchTextAfter</PRE></TD></TR><TR><TD><HR></TD></TR></TABLE><!-- BBCode End -->", $message);
	}

	// [QUOTE] and [/QUOTE] for posting replies with quote, or just for quoting stuff.
	$message = preg_replace("#\[quote\](.*?)\[/quote]#si", "<!-- BBCode Quote Start --><TABLE BORDER=0 ALIGN=CENTER WIDTH=85%><TR><TD>Quote:<HR></TD></TR><TR><TD><BLOCKQUOTE>\\1</BLOCKQUOTE></TD></TR><TR><TD><HR></TD></TR></TABLE><!-- BBCode Quote End -->", $message);

	// [b] and [/b] for bolding text.
	$message = preg_replace("#\[b\](.*?)\[/b\]#si", "<!-- BBCode Start --><B>\\1</B><!-- BBCode End -->", $message);

	// [i] and [/i] for italicizing text.
	$message = preg_replace("#\[i\](.*?)\[/i\]#si", "<!-- BBCode Start --><I>\\1</I><!-- BBCode End -->", $message);

	// [url]www.phpbb.com[/url] code..
	$message = preg_replace("#\[url\](http://)?(.*?)\[/url\]#si", "<!-- BBCode Start --><A HREF=\"http://\\2\" TARGET=\"_blank\">\\2</A><!-- BBCode End -->", $message);

	// [url=www.phpbb.com]phpBB[/url] code..
	$message = preg_replace("#\[url=(http://)?(.*?)\](.*?)\[/url\]#si", "<!-- BBCode Start --><A HREF=\"http://\\2\" TARGET=\"_blank\">\\3</A><!-- BBCode End -->", $message);

	// [email]user@domain.tld[/email] code..
	$message = preg_replace("#\[email\](.*?)\[/email\]#si", "<!-- BBCode Start --><A HREF=\"mailto:\\1\">\\1</A><!-- BBCode End -->", $message);

	// [img]image_url_here[/img] code..
	$message = preg_replace("#\[img\](http://)?(.*?)\[/img\]#si", "<!-- BBCode Start --><IMG SRC=\"http://\\2\"><!-- BBCode End -->", $message);
	// $message = preg_replace("#\[img\](.*?)\[/img\]#si", "<!-- BBCode Start --><IMG SRC=\"\\1\"><!-- BBCode End -->", $message);


	// unordered list code..
	$matchCount = preg_match_all("#\[list\](.*?)\[/list\]#si", $message, $matches);

	for ($i = 0; $i < $matchCount; $i++)
	{
		$currMatchTextBefore = preg_quote($matches[1][$i]);
		$currMatchTextAfter = preg_replace("#\[\*\]#si", "<LI>", $matches[1][$i]);

		$message = preg_replace("#\[list\]$currMatchTextBefore\[/list\]#si", "<!-- BBCode ulist Start --><UL>$currMatchTextAfter</UL><!-- BBCode ulist End -->", $message);
	}

	// ordered list code..
	$matchCount = preg_match_all("#\[list=([a1])\](.*?)\[/list\]#si", $message, $matches);

	for ($i = 0; $i < $matchCount; $i++)
	{
		$currMatchTextBefore = preg_quote($matches[2][$i]);
		$currMatchTextAfter = preg_replace("#\[\*\]#si", "<LI>", $matches[2][$i]);

		$message = preg_replace("#\[list=([a1])\]$currMatchTextBefore\[/list\]#si", "<!-- BBCode olist Start --><OL TYPE=\\1>$currMatchTextAfter</OL><!-- BBCode olist End -->", $message);
	}

	return($message);
}

?>