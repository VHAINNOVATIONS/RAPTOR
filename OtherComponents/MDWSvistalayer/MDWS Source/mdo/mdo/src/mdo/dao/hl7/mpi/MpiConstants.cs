using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo.dao.hl7.components;

namespace gov.va.medora.mdo.dao.hl7.mpi
{
    public class MpiConstants
    {
        public const string VQQ_SENDING_APP = "MPI_LOAD";
        //public const string VQQ_SENDING_FACILITY = "500";
        public const string VQQ_SENDING_FACILITY = "200NVW";
        public const string VQQ_RECEIVING_APP = "MPI-ICN";
        public const string VQQ_RECEIVING_FACILITY = "200M";
        public const string VQQ_MSG_CODE = "VQQ";
        public const string VQQ_TRIGGER = "Q02";
        public const string VQQ_MSG_CTL = "100000082-1";
        public const string VQQ_PROCESSING_ID = "P";
        public const string VQQ_VERSION_ID = "2.3";
        public const string VQQ_ACCEPT_ACK_TYPE = "AL";
        public const string VQQ_APP_ACK_TYPE = "AL";
        public const string VQQ_COUNTRY_CODE = "USA";

        public const string VQQ_QUERY_TAG = "MDWS";
        public const string VQQ_FORMAT_CODE = "T";
        public const string VQQ_QUERY_NAME_EXACT = "VTQ_PID_ICN_NO_LOAD";
        public const string VQQ_QUERY_NAME_FUZZY = "VTQ_DISPLAY_ONLY_QUERY";
        public const string VQQ_VIRTUAL_TABLE = "ICN";

        public const string LASTNAME_FLDNAME = "@00108.1";
        public const string FIRSTNAME_FLDNAME = "@00108.2";
        public const string MIDDLENAME_FLDNAME = "@00108.3";
        public const string NAMESUFFIX_FLDNAME = "@00108.4";
        public const string SSN_FLDNAME = "@00122";
        public const string DOB_FLDNAME = "@00110";
        public const string SEX_FLDNAME = "@00111";
        public const string ICN_FLDNAME = "@00105";
        public const string CMOR_FLDNAME = "@00756";
        public const string SITES_FLDNAME = "@00169";
        public const string DECEASEDDATE_FLDNAME = "@00740";

        public static ColumnDescription FLD_LASTNAME = new ColumnDescription("@00108.1", "ST", 30);
        public static ColumnDescription FLD_SSN = new ColumnDescription("@00122", "ST", 9);
        public static ColumnDescription FLD_DOB = new ColumnDescription("@00110", "TS", 8);
        public static ColumnDescription FLD_FIRSTNAME = new ColumnDescription("@00108.2", "ST", 30);
        public static ColumnDescription FLD_DECEASED_DATE = new ColumnDescription("@00740", "TS", 8);
        public static ColumnDescription FLD_MIDDLENAME = new ColumnDescription("@00108.3", "ST", 16);
        public static ColumnDescription FLD_SEX = new ColumnDescription("@00111", "ST", 1);
        public static ColumnDescription FLD_POB_CITY = new ColumnDescription("@00126.1", "ST", 30);
        public static ColumnDescription FLD_POB_STATE = new ColumnDescription("@00126.2", "ST", 3);
        public static ColumnDescription FLD_NAME_SUFFIX = new ColumnDescription("@00108.4", "ST", 10);
        public static ColumnDescription FLD_ICN = new ColumnDescription("@00105", "ST", 19);
        public static ColumnDescription FLD_CMOR = new ColumnDescription("@00756", "ST", 6);
        public static ColumnDescription FLD_SITES = new ColumnDescription("@00169", "ST", 999);
    }
}
