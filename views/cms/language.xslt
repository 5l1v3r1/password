<?xml version="1.0" ?>
<!--
//
//  views/cms/language.xslt
//
//  Copyright (C) by Hugo Leisink <hugo@leisink.net>
//  This file is part of the Banshee PHP framework
//  http://www.banshee-php.org/
//
//-->
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:include href="../banshee/main.xslt" />
<xsl:include href="../banshee/tablemanager.xslt" />

<xsl:template match="content">
<xsl:apply-templates select="tablemanager" />
</xsl:template>

</xsl:stylesheet>
