<?xml version="1.0" ?>
<!--
//
//  views/logout.xslt
//
//  Copyright (C) by Hugo Leisink <hugo@leisink.net>
//  This file is part of the Banshee PHP framework
//  http://www.banshee-php.org/
//
//-->
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:include href="banshee/main.xslt" />

<!--
//
//  Content template
//
//-->
<xsl:template match="content">
<h1>Logout</h1>
<xsl:apply-templates select="result" />
</xsl:template>

</xsl:stylesheet>
