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
						<xsl:apply-templates select="/ROOT/CLIENT_AREA/COUNTRY_CARD/COUNTRY" />
						<div class="feedback">
							<xsl:apply-templates select="/ROOT/CLIENT_AREA/L_STATIC_PAGE_LIST" />
							<xsl:apply-templates select="/ROOT/CLIENT_AREA/L_COMMENTS" />
							<xsl:call-template name="Diploma" />
						</div>
					</div>
					<div class="clearfloat"></div>
					<div class="empty"></div>
				</div>
				<xsl:call-template name ="Footer" />
				<xsl:call-template name ="counter" />
			</body>
		</html>	
  </xsl:template>

	<xsl:template match="/ROOT/CLIENT_AREA/L_STATIC_PAGE_LIST">
		<xsl:if test="STATICS">
			<h1>Статьи</h1>
			<ul>
			<xsl:for-each select="STATICS">
					<li><a href="{LayerLinks/Link}"><xsl:value-of select="NAME" disable-output-escaping="yes" /></a></li>
			</xsl:for-each>
			</ul>
		</xsl:if>
	</xsl:template>

	<xsl:template match="/ROOT/CLIENT_AREA/L_COMMENTS">
		<xsl:if test="COMMENT">
			<h1>Отзывы</h1>
			<xsl:for-each select="COMMENT">
				<a href="{LayerLinks/Link[@name='comment']}"><h2><xsl:value-of select="NAME" disable-output-escaping="yes" /></h2></a>
				<xsl:if test="(IMG_WIDTH &gt; 0) and (IMG_HEIGHT &gt; 0)">
					<div class="img"><a href="{LayerLinks/Link[@name='comment']}"><img src="{LayerLinks/Link[@name='image']}" class="c" title="{NAME}" alt="{NAME}" width="{IMG_WIDTH}" height="{IMG_HEIGHT}" /></a></div>
				</xsl:if>
				<p><xsl:value-of select="ANNOTATION" disable-output-escaping="yes" /></p>
			</xsl:for-each>
		</xsl:if>
	</xsl:template>

	<xsl:template match="/ROOT/CLIENT_AREA/COUNTRY_CARD/COUNTRY">
		<div class="country">
			<h1><xsl:value-of select="NAME" disable-output-escaping="yes" /></h1>
			<p class="subtitle"><xsl:value-of select="SUBTITLE" disable-output-escaping="yes" /></p>
			<xsl:value-of select="TEXT" disable-output-escaping="yes" />
		</div>
	</xsl:template>

	<xsl:template name="Diploma">
				<xsl:if test="(/ROOT/CLIENT_AREA/COUNTRY_CARD/COUNTRY/DIPLOMA_WIDTH &gt; 0) and (/ROOT/CLIENT_AREA/COUNTRY_CARD/COUNTRY/DIPLOMA_HEIGHT &gt; 0)">
					<div class="img">
						<hr width="80%" />
						<h1>Диплом</h1>
						<img src="{/ROOT/CLIENT_AREA/COUNTRY_CARD/COUNTRY/LayerLinks/Link[@name='diploma']}" 
							class="c" title="{/ROOT/CLIENT_AREA/COUNTRY_CARD/COUNTRY/DIPLOMA_NAME}" 
							alt="{/ROOT/CLIENT_AREA/COUNTRY_CARD/COUNTRY/DIPLOMA_NAME}" 
							width="{/ROOT/CLIENT_AREA/COUNTRY_CARD/COUNTRY/DIPLOMA_WIDTH}" 
							height="{/ROOT/CLIENT_AREA/COUNTRY_CARD/COUNTRY/DIPLOMA_HEIGHT}" 
						/>
					</div>
				</xsl:if>
	</xsl:template>

</xsl:stylesheet>