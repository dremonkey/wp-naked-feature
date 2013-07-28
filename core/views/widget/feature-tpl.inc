<?php
/**
 *	This is the template file that will be used with the featured_home backbone block
 */
?>

<script type="text/template" id="feature-item-tpl">

	<div class="inner">
		<a href="<%= permalink %>"></a>

		<% if( img_src ) { %>
			<img src="<%= img_src %>" alt="<%= post_title %>" />
		<% } else { %>
			<div class="placeholder-box"></div>
		<% } %>

		<div class="title-box">
			<h2 class="title"><%= post_title %></h2>
			<span class="nub"></span>
		</div>
	</div>

</script>