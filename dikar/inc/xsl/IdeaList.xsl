<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="HTML" doctype-system="about:legacy-compat" />
	<xsl:output encoding="windows-1251"/>

	<xsl:include href="templates.xsl" />

	<xsl:template match="ROOT">
		<html>
			<xsl:call-template name="Head">
				<xsl:with-param name="keywords" select="/ROOT/KEYWORDS" />
				<xsl:with-param name="description" select="/ROOT/DESCRIPTION" />
			</xsl:call-template>
			<body>
				<div class="root">
					<xsl:call-template name="Header" />
					<div class="text">
					<xsl:apply-templates select="/ROOT/CLIENT_AREA/L_IDEAS" />
					</div>
					<div class="clearfloat"></div>
					<div class="empty"></div>
				</div>
				<xsl:call-template name ="Footer" />
				<xsl:call-template name ="counter" />
			</body>
		</html>	
  </xsl:template>

	<xsl:template match="/ROOT/CLIENT_AREA/L_IDEAS">
		<xsl:if test="IDEA">
			<table align="center" width="100%" cellpadding="5" cellspacing="5" border="0">
				<col valign="top" />
				<col valign="top" width="100%" />
					<tr>
						<td></td>
						<td><h2>Идеи туров</h2><p>В этом разделе собраны описания индивидуальных туров, которые мы сделали для наших клиентов и которыми вы можете воспользоваться как идеями для своих путешествий.</p></td>
					</tr>
				<xsl:for-each select="IDEA">
					<tr>
						<td>
							<xsl:if test="(IMG_WIDTH &gt; 0) and (IMG_HEIGHT &gt; 0)">
								<div class="img"><a href="{LayerLinks/Link[@name='idea']}"><img src="{LayerLinks/Link[@name='image']}" class="c" title="{NAME}" alt="{NAME}" width="{IMG_WIDTH}" height="{IMG_HEIGHT}" /></a></div>
							</xsl:if>
						</td>
						<td valign="top">
							<a href="{LayerLinks/Link[@name='idea']}"><h2><xsl:value-of select="NAME" disable-output-escaping="yes" /></h2></a>
							<p><xsl:value-of select="ANNOTATION" disable-output-escaping="yes" /></p>
							<p><xsl:value-of select="COUNTRY__COUNTRY_ID/NAME" disable-output-escaping="yes" /></p>
						</td>
					</tr>
				</xsl:for-each>
			</table>
		</xsl:if>
	</xsl:template>

</xsl:stylesheet>