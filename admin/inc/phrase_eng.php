<?
/* .21
$MONTH_NAME = array(1=>"������", "�������", "����",

            "������", "���", "����", "����", "������",

            "��������", "�������", "������", "�������");
*/
$MONTH_NAME = array(1=>"������", "�������", "����",

            "������", "���", "����", "����", "������",

            "��������", "�������", "������", "�������");
for($i=1; $i<=12; $i++)
	{
	$MONTH_NAME[$i]=date("F", mktime(0,0,0, $i, 1, 2006));
	}

$MONTH_NAME_BY = array(1=>"������", "�������", "�����",

            "������", "���", "����", "����", "�������",

            "��������", "�������", "������", "�������");





$WEEK_NAME=array(1=>"��", "��", "��", "��", "��", "��", "��");

$WEEK_NAME_FULL=array(1=>"�����������", "�������", "�����", "�������", "�������", "�������", "�����������");

$ALL_ANGLE="��� ��������� �� ����";

$FIO="�.�.�. ��������������";

$SMI="�������� ���";

$SMI_TYPE="��� ���";

$PHONE="�������";

$EMAIL="E-mail";

$NECESSARY_FIELDS = "���������� (*) �������� ����, ������������ ��� ����������.";

$SEND="���������";

$QUESTION="������";

$ANSWER="�����";

$ALL_QUESTION="��� ������� �� ����";

$PHOTO="����";

$VIDEO="�����";

$AUDIO="�����";

$FULL_TEXT="������ �����";

$PRINT_V="������ ��� ������";

$VOTE_SEND="����������";

$VOTE_RES="���������� �����������";

$DOWNLOAD="������� ����";

$ALL_MATERIALS="��� ���������";


?>