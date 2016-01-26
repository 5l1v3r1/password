<?xml version="1.0" ?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:include href="banshee/main.xslt" />

<!--
//
//  Password template
//
//-->
<xsl:template match="password">
<table class="table table-striped table-condensed">
<tr><td>Name:</td><td><xsl:value-of select="name" /></td></tr>
<xsl:if test="url!=''">
<tr><td>URL:</td><td><a href="{url}"><xsl:value-of select="url" /></a></td></tr>
</xsl:if>
<xsl:if test="username!=''">
<tr><td>Username:</td><td><xsl:value-of select="username" /></td></tr>
</xsl:if>
<tr><td>Password:</td><td><input id="password" readonly="readonly" class="text">
<xsl:if test="/output/@iphone='yes'"><xsl:attribute name="onClick">this.setSelectionRange(0, this.value.length)</xsl:attribute></xsl:if>
<xsl:if test="/output/@iphone='no'"><xsl:attribute name="onClick">this.select()</xsl:attribute></xsl:if>
</input></td></tr>
<tr id="inforow"><td>Information:</td><td><span id="info"></span></td></tr>
</table>

<div class="btn-group">
<a href="/password/{@id}/edit" class="btn btn-default">Edit</a>
<a href="/container/{container_id}" class="btn btn-default">Back</a>
</div>
</xsl:template>

<!--
//
//  Edit template
//
//-->
<xsl:template match="edit">
<xsl:call-template name="show_messages" />
<form action="/{/output/page}/{password/container_id}" method="post">
<xsl:if test="password/@id">
<input type="hidden" name="id" value="{password/@id}" />
</xsl:if>
<xsl:if test="not(password/@id)">
<input type="hidden" name="container_id" value="{password/container_id}" />
</xsl:if>

<label for="name">Name:</label>
<input type="text" id="name" name="name" value="{password/name}" class="form-control" />
<label for="url">URL:</label>
<input type="text" id="url" name="url" value="{password/url}" class="form-control" />
<label for="username">Username:</label>
<input type="text" id="username" name="username" value="{password/username}" class="form-control" />
<label for="password">Password*:</label>
<input type="text" id="password" name="password" value="" class="form-control" />
<label for="info">Information*:</label>
<textarea id="info" name="info" class="form-control"><xsl:value-of select="password/info" /></textarea>
<xsl:if test="password/@id">
<label for="container">Container:</label>
<select id="container" name="container_id" class="form-control">
<xsl:for-each select="containers/container">
<option type="radio" name="container_id" value="{@id}">
<xsl:if test="@id=../../password/container_id"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
<xsl:value-of select="." />
</option>
</xsl:for-each>
</select>
</xsl:if>
<p>*: will be encrypted</p>

<div class="btn-group">
<input type="submit" name="submit_button" value="Save password" class="btn btn-default" />
<a href="/container/{password/container_id}" class="btn btn-default">Cancel</a>
<xsl:if test="password/@id">
<input type="submit" name="submit_button" value="Delete password" class="btn btn-default" onClick="javascript:return confirm('DELETE: Are you sure?')" />
</xsl:if>
<input type="button" value="Random password" class="btn btn-default" onClick="javascript:get_random_password()" />
</div>
</form>
</xsl:template>

<!--
//
//  Content template
//
//-->
<xsl:template match="content">
<h1>Password</h1>
<xsl:apply-templates select="crumbs" />
<xsl:apply-templates select="password" />
<xsl:apply-templates select="edit" />
<xsl:apply-templates select="result" />
</xsl:template>

</xsl:stylesheet>
