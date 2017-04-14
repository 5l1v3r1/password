<?xml version="1.0" ?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:include href="functions.xslt" />
<xsl:include href="layout_cms.xslt" />
<xsl:include href="layout_site.xslt" />

<xsl:output method="html" doctype-system="about:legacy-compat"/>

<xsl:template match="/output">
<xsl:apply-templates select="layout_cms" />
<xsl:apply-templates select="layout_site" />
</xsl:template>

</xsl:stylesheet>
