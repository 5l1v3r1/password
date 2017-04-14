<?xml version="1.0" ?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:include href="banshee/main.xslt" />

<!--
//
//  Search template
//
//-->
<xsl:template name="search">
<div class="search">
<form action="/{/output/page}" method="post">
<input type="text" id="search" name="search" placeholder="Search" class="form-control" />
<input type="hidden" name="submit_button" value="Search" />
</form>
</div>
</xsl:template>

<!--
//
//  Overview template
//
//-->
<xsl:template match="overview">
<table class="table table-striped table-condensed">
<thead>
<tr><th></th><th>Name</th><th></th></tr>
</thead>
<tbody>
<xsl:if test="@id!=0">
<tr class="click">
<td><img src="/images/directory.png" alt="dir" /></td>
<td><a href="/{/output/page}/{@parent_id}" class="cell">..</a></td>
<td></td>
</tr>
</xsl:if>
<xsl:for-each select="container">
<tr>
<td><img src="/images/directory.png" alt="dir" /></td>
<td><a href="/{/output/page}/{@id}" class="cell"><xsl:value-of select="name" /></a></td>
<td><a href="/{/output/page}/{@id}/edit">edit</a></td>
</tr>
</xsl:for-each>
<xsl:for-each select="password">
<tr>
<td><img src="/images/file.png" alt="file" /></td>
<td><xsl:value-of select="path" /> <a href="/password/{@id}" class="cell"><xsl:value-of select="name" /></a></td>
<td><a href="/password/{@id}/edit">edit</a></td>
</tr>
</xsl:for-each>
</tbody>
</table>
<xsl:apply-templates select="pagination" />

<xsl:if test="@id">
<div class="btn-group">
<xsl:if test="@id!=0">
<a href="/password/{@id}/new" class="btn btn-default">New password</a>
</xsl:if>
<a href="/{/output/page}/{@id}/new" class="btn btn-default">New container</a>
</div>
</xsl:if>
</xsl:template>

<!--
//
//  Edit template
//
//-->
<xsl:template match="edit">
<xsl:call-template name="show_messages" />
<form action="/{/output/page}/{container/parent_id}" method="post">
<xsl:if test="container/@id">
<input type="hidden" name="id" value="{container/@id}" />
</xsl:if>
<xsl:if test="not(container/@id)">
<input type="hidden" name="parent_id" value="{container/parent_id}" />
</xsl:if>

<table class="edit">
<label for="name">Container:</label>
<input type="text" id="name" name="name" value="{container/name}" class="form-control" autofocus="autofocus" />
<xsl:if test="container/@id">
<label for="parent">Parent container:</label>
<select id="parent" name="parent_id" class="form-control">
<xsl:for-each select="containers/container">
<option value="{@id}">
<xsl:if test="@id=../../container/parent_id"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
<xsl:value-of select="." />
</option>
</xsl:for-each>
</select>
</xsl:if>
</table>

<div class="btn-group">
<input type="submit" name="submit_button" value="Save container" class="btn btn-default" />
<a href="/{/output/page}/{container/parent_id}" class="btn btn-default">Cancel</a>
<xsl:if test="container/@id">
<input type="submit" name="submit_button" value="Delete container" class="btn btn-default" onClick="javascript:return confirm('DELETE: Are you sure?')" />
</xsl:if>
</div>
</form>
</xsl:template>

<!--
//
//  Content template
//
//-->
<xsl:template match="content">
<h1>Passwords</h1>
<xsl:call-template name="search" />
<xsl:apply-templates select="crumbs" />
<xsl:apply-templates select="overview" />
<xsl:apply-templates select="edit" />
<xsl:apply-templates select="result" />
</xsl:template>

</xsl:stylesheet>
