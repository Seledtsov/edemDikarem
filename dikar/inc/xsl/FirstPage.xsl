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
					<xsl:call-template name="FirstPage" />
					<div class="clearfloat"></div>
					<div class="empty"></div>
				</div>
				<xsl:call-template name ="Footer" />
				<xsl:call-template name ="counter" />
			</body>
		</html>
	</xsl:template>

	<xsl:template name="FirstPage">
		<div class="f_page">
			<div class="f_content">
				<div class="text">
					<xsl:apply-templates select="/ROOT/CLIENT_AREA/FP_IMAGE/FP_PHOTO" />
					<xsl:apply-templates select="/ROOT/CLIENT_AREA/FP_NEWS_LIST" />
				</div>
			</div>
			<div class="f_left-sidebar">
				<div class="text">
					<h1><xsl:value-of disable-output-escaping="yes" select="/ROOT/CLIENT_AREA/L_STATIC_PAGE/STATICS/NAME" /></h1>
					<xsl:value-of disable-output-escaping="yes" select="/ROOT/CLIENT_AREA/L_STATIC_PAGE/STATICS/FULL_TEXT" />
				</div>
			</div>
			<div class="f_right-sidebar">
					<xsl:apply-templates select="/ROOT/CLIENT_AREA/FP_COUNTRIES" />
			</div>
		</div>
	</xsl:template>


	<xsl:template match="/ROOT/CLIENT_AREA/FP_IMAGE/FP_PHOTO">
		<xsl:if test="(IMG_WIDTH &gt; 0) and (IMG_HEIGHT &gt; 0)">
			<div class="week_img">
				<div class="label newsBlockTitle">Фото недели</div>
				<img src="{LayerLinks/Link[@name='image']}" title="{NAME}" alt="{NAME}" width="{IMG_WIDTH}" height="{IMG_HEIGHT}" />
			</div>
		</xsl:if>
	</xsl:template>	

	
	<xsl:template match="/ROOT/CLIENT_AREA/FP_NEWS_LIST">
		<xsl:if test="NEWS">
			<div class="label newsBlockTitle">Новости</div>
			<table class="newsList">
				<xsl:for-each select="NEWS">
					<tr>
						<td class="dateNews">
							<xsl:variable name="day" select="DATE_MAIN/DAY" />
							<xsl:variable name="month" select="DATE_MAIN/MONTH" />
							<xsl:variable name="year" select="DATE_MAIN/YEAR" />
							<xsl:call-template name="dateNews">
								<xsl:with-param name="day"><xsl:value-of select="$day" /></xsl:with-param>
								<xsl:with-param name="month"><xsl:value-of select="$month" /></xsl:with-param>
								<xsl:with-param name="year"><xsl:value-of select="$year" /></xsl:with-param>
							</xsl:call-template>
						</td>
						<td>
							<xsl:value-of select="NAME" disable-output-escaping="yes" />
						</td>
					</tr>
				</xsl:for-each>
			</table>
		</xsl:if>
	</xsl:template>

	<xsl:template match="/ROOT/CLIENT_AREA/FP_COUNTRIES">
		<xsl:for-each select="FP_COUNTRIES_IMAGES">
			<xsl:if test="(IMG_WIDTH &gt; 0) and (IMG_HEIGHT &gt; 0)">
				<div class="country_img">
					<div class="country_label newsBlockTitle"><xsl:value-of select="NAME" disable-output-escaping="yes" /></div>
					<!-- img src="{LayerLinks/Link[@name='image']}" title="{NAME}" alt="{NAME}" width="{IMG_WIDTH}" height="{IMG_HEIGHT}" / -->
					<a href="{LayerLinks/Link[@name='page']}">
						<img src="{LayerLinks/Link[@name='image']}" title="{NAME}" alt="{NAME}" width="155" height="103" />
					</a>
				</div>
			</xsl:if>
		</xsl:for-each>
	</xsl:template>

</xsl:stylesheet>