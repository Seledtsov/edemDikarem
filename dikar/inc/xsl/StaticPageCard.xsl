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
				<xsl:with-param name="title" select="/ROOT/CLIENT_AREA/L_STATIC_PAGE/STATICS/NAME" />
			</xsl:call-template>
			<body>
				<div class="root">
					<xsl:call-template name="Header" />
					<div class="text">
						<xsl:apply-templates select="/ROOT/CLIENT_AREA/L_STATIC_PAGE/STATICS" />
					</div>
					<div class="clearfloat"></div>
					<div class="empty"></div>
				</div>
				<xsl:call-template name ="Footer" />
				<xsl:call-template name ="counter" />
			</body>
		</html>	
	</xsl:template>

	<xsl:template match="/ROOT/CLIENT_AREA/L_STATIC_PAGE/STATICS">
		<h1><xsl:value-of select="NAME" disable-output-escaping="yes" /></h1>		
		<xsl:value-of select="FULL_TEXT" disable-output-escaping="yes" />
	</xsl:template>

</xsl:stylesheet>