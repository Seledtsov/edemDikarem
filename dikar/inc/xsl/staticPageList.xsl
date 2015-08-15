<?xml version="1.0" encoding="windows-1251"?>

<xsl:stylesheet
	version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
>

<xsl:output
	doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"
	doctype-system="DTD/xhtml1-strict.dtd"
	indent="yes"
	encoding="Windows-1251"
/>

<xsl:include href="default.xsl" />

<xsl:template match="L_STATIC_LIST">
	<table class="overlined useHighLight">
		<xsl:for-each select="STATICS">
			<tr>
				<td>
					<a href="{.//Link}">
						<xsl:value-of disable-output-escaping="yes" select="NAME" />
					</a>
				</td>
			</tr>
		</xsl:for-each>
	</table>
</xsl:template>

</xsl:stylesheet>