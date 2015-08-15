<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="HTML" doctype-system="about:legacy-compat" />
	<xsl:output encoding="windows-1251"/>

	<xsl:include href="templates.xsl" />

	<xsl:template match="ROOT">
		<html>
			<xsl:call-template name="Head">
				<xsl:with-param name="keywords" select="/ROOT/CLIENT_AREA/L_COMMENT/COMMENT/KEYWORDS" />
				<xsl:with-param name="description" select="/ROOT/CLIENT_AREA/L_COMMENT/COMMENT/DESCRIPTION" />
				<xsl:with-param name="title" select="/ROOT/CLIENT_AREA/L_COMMENT/COMMENT/NAME" />
			</xsl:call-template>
			<body>
				<div class="root">
					<xsl:call-template name="Header" />
					<div class="text">
					<xsl:apply-templates select="/ROOT/CLIENT_AREA/L_COMMENT/COMMENT" />
					</div>
					<div class="clearfloat"></div>
					<div class="empty"></div>
				</div>
				<xsl:call-template name ="Footer" />
				<xsl:call-template name ="counter" />
			</body>
		</html>	
  </xsl:template>

	<xsl:template match="/ROOT/CLIENT_AREA/L_COMMENT/COMMENT">
		<div class="comment">
			<h1><xsl:value-of select="NAME" disable-output-escaping="yes" /></h1>
			<p class="subtitle"><xsl:value-of select="SUBNAME" disable-output-escaping="yes" /></p>
			<xsl:value-of select="TEXT" disable-output-escaping="yes" />
		</div>
	</xsl:template>

</xsl:stylesheet>