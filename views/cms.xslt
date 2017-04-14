<?xml version="1.0" ?>
<!--
//
//  views/cms.xslt
//
//  Copyright (C) by Hugo Leisink <hugo@leisink.net>
//  This file is part of the Banshee PHP framework
//  http://www.banshee-php.org/
//
//-->
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:include href="banshee/main.xslt" />

<xsl:template match="content">
<h1>Website administration</h1>
<xsl:for-each select="menu/section">
	<xsl:if test="count(entry[@access='yes'])>0">
		<div class="panel panel-default panel_{@class}">
		<div class="panel-heading"><xsl:value-of select="@text" /></div>
		<ul class="panel-body">
		<xsl:for-each select="entry[@access='yes']">
			<li><a href="/{.}"><img src="/images/icons/{@icon}" class="icon" /><xsl:value-of select="@text" /></a></li>
		</xsl:for-each>
		</ul>
		</div>
	</xsl:if>
</xsl:for-each>
<br class="break" />
</xsl:template>

</xsl:stylesheet>
