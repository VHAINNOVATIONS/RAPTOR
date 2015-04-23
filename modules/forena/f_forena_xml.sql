CREATE OR REPLACE FUNCTION f_forena_xml(sql_cmd varchar2, rowset_tag VARCHAR2 DEFAULT 'table', p_max_rows NUMBER DEFAULT NULL) return XMLTYPE is
   INVALID_PACKAGE_STATE EXCEPTION;
   PRAGMA EXCEPTION_INIT (INVALID_PACKAGE_STATE, -4068);
   xmldoc xmltype;
   ctx NUMBER; 
   sql_err_no NUMBER;
   sql_err_msg VARCHAR2(1000); 
BEGIN
   -- Initialze the context
   ctx := DBMS_XMLQUERY.newContext(sql_cmd);
   DBMS_XMLQUERY.setrowsettag(ctx,rowset_tag); 
   DBMS_XMLQUERY.settagcase(ctx,dbms_xmlquery.lower_case);
   IF p_max_rows IS NOT NULL THEN 
     dbms_xmlQuery.Setmaxrows(ctx,p_max_rows); 
     END IF; 
   DBMS_XMLQUERY.setDateFormat(ctx,'yyyy-MM-dd HH:mm:ss');      
   DBMS_XMLQUERY.Setraiseexception(ctx,true); 
   DBMS_XMLQUERY.propagateoriginalexception(ctx,true); 
   DBMS_XMLQUERY.setraisenorowsexception(ctx,false); 
   xmldoc :=  xmltype(DBMS_XMLQUERY.GETXML(ctx)); 
   DBMS_XMLQUERY.Closecontext(ctx); 

   return xmldoc; 
   EXCEPTION 
     WHEN OTHERS THEN 
           DBMS_XMLQUERY.GETEXCEPTIONCONTENT(ctx,sql_err_no,sql_err_msg); 
            IF sql_err_no=4061 THEN 
             RAISE INVALID_PACKAGE_STATE; 
             END IF; 
            DBMS_OUTPUT.put_line(sqlerrm); 
            IF sql_err_no = 0 THEN 
              sql_err_msg := SQLERRM; 
              END IF; 
            SELECT XMLELEMENT("error",xmlattributes(sql_err_no "code"), sql_err_msg) INTO XMLDOC from dual; 
            RETURN xmldoc; 
END f_forena_xml;
/
--CREATE OR REPLACE PUBLIC SYNONYM f_forena_xml FOR f_forena_xml; 

