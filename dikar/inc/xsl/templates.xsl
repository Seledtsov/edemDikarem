<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:output method="html" indent="yes" encoding="windows-1251"/>

	<xsl:template name="Head">
		<xsl:param name="keywords" />
		<xsl:param name="description" />
		<xsl:param name="title" />

    <head>
    	<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />  
    	<meta http-equiv="X-UA-Compatible" content="IE=9" />

			<title>
				<xsl:if test="$title"> 
						<xsl:value-of select="$title" /><xsl:text>. </xsl:text> 
				</xsl:if> 
				<xsl:value-of select="/ROOT/NAVIGATION_LIST/NAVIGATION[ID=/ROOT/NAV_ID]/NAME" />. еДем Дикарем</title>

			<link rel="stylesheet" type="text/css" href="/css/css.css" />
			<xsl:if test="$keywords">
				<meta name="Keywords">
				  <xsl:attribute name="content">
				    <xsl:value-of select="$keywords" />
				  </xsl:attribute>
				</meta>
			</xsl:if>
			<xsl:if test="$description">
				<meta name="Description">
				  <xsl:attribute name="content">
				    <xsl:value-of select="$description" />
				  </xsl:attribute>
				</meta>
			</xsl:if>
			<xsl:call-template name="GA" />
    </head>
  </xsl:template>

	<xsl:template name="Header">
		<div class="collage">
			<div class="logo"><a href="/"><img src="/images/logo.png" border="0" /></a></div>
			<div class="email"><a href="mailto:info@edemdikarem.ru">info@edemdikarem.ru</a></div>
			<div class="menu">
				<xsl:call-template name="MainNavigation" />
			</div>
		</div>
	</xsl:template>

	<xsl:template name="Footer">
		<div class="footer">
			ООО «Едем дикарём» | <a href="mailto:info@edemdikarem.ru">info@edemdikarem.ru</a>
		</div>
	</xsl:template>

	<xsl:template name="MainNavigation">
		<ul>
			<xsl:for-each select="/ROOT/NAVIGATION_LIST/NAVIGATION[PARENT_ID=26]">
				<xsl:sort data-type="number" select="REAL_SORT_ORDER" />
				<xsl:variable name="id" select="ID" />
				<xsl:choose>
					<xsl:when test="(/ROOT/NAV_ID = ID)">
						<li class="selected">
							<a href="{LINK}"><xsl:value-of select="NAME" /></a>
						</li>
					</xsl:when>
					<xsl:otherwise>
						<li>
							<a href="{LINK}"><xsl:value-of select="NAME" /></a>
							<xsl:if test="/ROOT/NAVIGATION_LIST/NAVIGATION[PARENT_ID=$id]">
								<ul>
									<xsl:for-each select="/ROOT/NAVIGATION_LIST/NAVIGATION[PARENT_ID=$id]">
										<xsl:sort data-type="number" select="REAL_SORT_ORDER" />
										<li>
											<a href="{LINK}"><xsl:value-of select="NAME" /></a>
										</li>
									</xsl:for-each>
								</ul>
							</xsl:if>
						</li>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:for-each>
		</ul>
	</xsl:template>

	<xsl:template name="dateNews">
		<xsl:param name="day" />
		<xsl:param name="month" />
		<xsl:param name="year" />
		
		<span class="dateNews">
			<xsl:choose>
				<xsl:when test="($day &lt; 10) and ($month &lt; 10)">
					0<xsl:value-of select="$day" />.0<xsl:value-of select="$month" />.<xsl:value-of select="substring($year,3,2)" />
				</xsl:when>
				<xsl:when test="$day &lt; 10">
					0<xsl:value-of select="$day" />.<xsl:value-of select="$month" />.<xsl:value-of select="substring($year,3,2)" />
				</xsl:when>
				<xsl:when test="$month &lt; 10">
					<xsl:value-of select="$day" />.0<xsl:value-of select="$month" />.<xsl:value-of select="substring($year,3,2)" />
				</xsl:when>
				<xsl:otherwise>
					<xsl:value-of select="$day" />.<xsl:value-of select="$month" />.<xsl:value-of select="substring($year,3,2)" />
				</xsl:otherwise>
			</xsl:choose>
		</span>
	</xsl:template>

	<xsl:template name="counter">
		<span id="openstat2124763"></span>
		<script type="text/javascript">
			var openstat = { counter: 2124763, next: openstat };
			(function(d, t, p) {
				var j = d.createElement(t); j.async = true; j.type = "text/javascript";
				j.src = ("https:" == p ? "https:" : "http:") + "//openstat.net/cnt.js";
				var s = d.getElementsByTagName(t)[0]; s.parentNode.insertBefore(j, s);
				})(document, "script", document.location.protocol);
		</script>
	</xsl:template>

	<xsl:template name="GA">
<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-26168212-1']);
  _gaq.push(['_setDomainName', 'edemdikarem.ru']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>
	</xsl:template>

</xsl:stylesheet>