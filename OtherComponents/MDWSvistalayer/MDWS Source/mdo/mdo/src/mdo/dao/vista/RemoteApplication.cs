using System;
using System.Collections.Generic;
using System.Collections;
using gov.va.medora.utils;

namespace gov.va.medora.mdo.dao.vista
{
    public class RemoteApplication : VistaFile
    {
        const int NFLDS = 4;
        static string FILE_NUMBER = "8994.5";
        static string CALLBACK_FILE_NUMBER = "8994.51";

        List<RemoteApplicationRecord> records;

        public RemoteApplication()
        {
            FileNumber = "8994.5";
            FileName = "REMOTE APPLICATION";
            Global = "^XWB(8994.5,";
            records = new List<RemoteApplicationRecord>();
        }

        public List<RemoteApplicationRecord> Records
        {
            get { return records; }
        }

        public void getRecords(AbstractConnection cxn)
        {
            DdrLister query = buildGetRecordsQuery(cxn);
            string[] response = query.execute();
            toRecords(cxn, response);
        }

        internal DdrLister buildGetRecordsQuery(AbstractConnection cxn)
        {
            DdrLister query = new DdrLister(cxn);
            query.File = FileNumber;
            query.Fields = ".01;.02;.03";
            query.Flags = "IP";
            query.Xref = "#";
            return query;
        }

        internal void toRecords(AbstractConnection cxn, string[] response)
        {
            if (response == null || response.Length == 0)
            {
                return;
            }
            records = new List<RemoteApplicationRecord>(response.Length);
            for (int i = 0; i < response.Length; i++)
            {
                RemoteApplicationRecord rec = toRecord(cxn, response[i]);
                records.Add(rec);
            }
        }

        internal RemoteApplicationRecord toRecord(AbstractConnection cxn, string response)
        {
            string[] flds = StringUtils.split(response, StringUtils.CARET);
            RemoteApplicationRecord rec = new RemoteApplicationRecord();
            rec.Fields["IEN"].VistaValue = flds[0];
            rec.Fields["NAME"].VistaValue = flds[1];
            rec.Fields["CONTEXTOPTION"].VistaValue = flds[2];
            rec.Fields["APPLICATIONCODE"].VistaValue = flds[3];
            rec.CallBackFile.getRecords(cxn, flds[0]);
            return rec;
        }

        internal RemoteApplicationRecord toRecord(AbstractConnection cxn, string ien, string response)
        {
            response = ien + '^' + response;
            return toRecord(cxn, response);
        }

        public RemoteApplicationRecord getRecord(AbstractConnection cxn, KeyValuePair<string, string> param)
        {
            string ien = "";
            string arg = "";
            if (param.Key == "IEN")
            {
                ien = param.Value;
            }
            else if (param.Key == "NAME")
            {
                arg = "$O(^XWB(8994.5,\"B\",\"" + param.Value + "\",0))";
                ien = VistaUtils.getVariableValue(cxn, arg);
            }
            else if (param.Key == "APPLICATIONCODE")
            {
                arg = "$O(^XWB(8994.5,\"ACODE\",\"" + param.Value + "\",0))";
                ien = VistaUtils.getVariableValue(cxn, arg);
            }
            else
            {
                throw new ArgumentException("Invalid field name");
            }
            if (ien == "")
            {
                return null;
            }
            arg = "$G(^XWB(8994.5," + ien + ",0))";
            string response = VistaUtils.getVariableValue(cxn, arg);
            return toRecord(cxn, ien, response);
        }

        public static string addRecord(AbstractConnection cxn, RemoteApplicationRecord rec, CallBackFileRecord subrec)
        {
            DdrFiler query = buildAddRecordQuery(cxn, rec, subrec);
            string response = query.execute();
            return response;
        }

        internal static DdrFiler buildAddRecordQuery(AbstractConnection cxn, RemoteApplicationRecord rec, CallBackFileRecord subrec)
        {
            DdrFiler query = new DdrFiler(cxn);
            query.Operation = "ADD";
            ArrayList lst = new ArrayList(7);
            VistaField f = rec.Fields["NAME"];
            lst.Add(FILE_NUMBER + "^" + f.VistaNumber + "^+1,^" + f.VistaValue);
            f = rec.Fields["CONTEXTOPTION"];
            VistaUserDao dao = new VistaUserDao(cxn);
            lst.Add(FILE_NUMBER + "^" + f.VistaNumber + "^+1,^" + dao.getOptionIen(f.VistaValue));
            f = rec.Fields["APPLICATIONCODE"];
            lst.Add(FILE_NUMBER + "^" + f.VistaNumber + "^+1,^" + f.VistaValue);

            f = subrec.Fields["CALLBACKTYPE"];
            lst.Add(CALLBACK_FILE_NUMBER + "^" + f.VistaNumber + "^+2,+1^" + f.VistaValue);
            f = subrec.Fields["CALLBACKPORT"];
            lst.Add(CALLBACK_FILE_NUMBER + "^" + f.VistaNumber + "^+2,+1^" + f.VistaValue);
            f = subrec.Fields["CALLBACKSERVER"];
            lst.Add(CALLBACK_FILE_NUMBER + "^" + f.VistaNumber + "^+2,+1^" + f.VistaValue);
            f = subrec.Fields["URLSTRING"];
            lst.Add(CALLBACK_FILE_NUMBER + "^" + f.VistaNumber + "^+2,+1^" + f.VistaValue);

            query.Args = (string[])lst.ToArray(typeof(string));
            return query;
        }

        public static string deleteRecord(AbstractConnection cxn, string ien)
        {
            CallBackFile callBackFile = new CallBackFile();
            string result = callBackFile.deleteRecord(cxn, "1", ien);
            DdrFiler query = buildDeleteRecordQuery(cxn, ien);
            result = query.execute();
            return result;
        }

        internal static DdrFiler buildDeleteRecordQuery(AbstractConnection cxn, string ien)
        {
            DdrFiler query = new DdrFiler(cxn);
            query.Operation = "EDIT";
            query.Args = new string[]
            {
                FILE_NUMBER + "^.01^" + ien + ",^@"
            };
            return query;
        }
    }

    public class RemoteApplicationRecord
    {
        const int NFLDS = 4;

        Dictionary<string, VistaField> fields;
        CallBackFile callBackFile;

        public RemoteApplicationRecord()
        {
            setFields();
            callBackFile = new CallBackFile();
        }

        void setFields()
        {
            fields = new Dictionary<string, VistaField>(NFLDS);
            VistaField f = new VistaField();
            f.VistaName = "IEN";
            fields.Add(f.VistaName, f);

            f = new VistaField();
            f.VistaNumber = ".01";
            f.VistaName = "NAME";
            f.VistaNode = "0";
            f.VistaPiece = "1";
            fields.Add(f.VistaName, f);

            f = new VistaField();
            f.VistaNumber = ".02";
            f.VistaName = "CONTEXTOPTION";
            f.VistaNode = "0";
            f.VistaPiece = "2";
            fields.Add(f.VistaName, f);

            f = new VistaField();
            f.VistaNumber = ".03";
            f.VistaName = "APPLICATIONCODE";
            f.VistaNode = "0";
            f.VistaPiece = "3";
            fields.Add(f.VistaName, f);
        }

        public Dictionary<string, VistaField> Fields
        {
            get { return fields; }
        }

        public CallBackFile CallBackFile
        {
            get { return callBackFile; }
            set { callBackFile = value; }
        }
    }

    public class CallBackFile : VistaFile
    {
        const string FILE_NUMBER = "8994.51";

        List<CallBackFileRecord> records;

        public CallBackFile()
        {
            FileNumber = FILE_NUMBER;
            FileName = "CALLBACKTYPE";
            Global = "";
            records = new List<CallBackFileRecord>();
        }

        public List<CallBackFileRecord> Records
        {
            get { return records; }
        }

        public void getRecords(AbstractConnection cxn, string ien)
        {
            DdrLister query = buildGetSubrecordsQuery(cxn, ien);
            string[] response = query.execute();
            toRecords(response);
        }

        internal DdrLister buildGetSubrecordsQuery(AbstractConnection cxn, string ien)
        {
            DdrLister query = new DdrLister(cxn);
            query.File = FILE_NUMBER;
            query.Iens = ',' + ien + ',';
            query.Fields = ".01;.02;.03;.04";
            query.Flags = "IP";
            query.Xref = "#";
            return query;
        }

        public string deleteRecord(AbstractConnection cxn, string ien, string parentIen)
        {
            DdrFiler query = buildRemoveSubrecordQuery(cxn, ien, parentIen);
            string response = query.execute();
            return response;
        }

        internal DdrFiler buildRemoveSubrecordQuery(AbstractConnection cxn, string ien, string parentIen)
        {
            DdrFiler query = new DdrFiler(cxn);
            query.Operation = "EDIT";
            query.Args = new string[]
            {
                FILE_NUMBER + "^.01^" + ien + ',' + parentIen + ",^@"
            };
            return query;
        }

        internal void toRecords(string[] response)
        {
            if (response == null || response.Length == 0)
            {
                return;
            }
            records = new List<CallBackFileRecord>(response.Length);
            for (int i = 0; i < response.Length; i++)
            {
                string[] flds = StringUtils.split(response[i], StringUtils.CARET);
                CallBackFileRecord rec = new CallBackFileRecord();
                rec.Fields["IEN"].VistaValue = flds[0];
                rec.Fields["CALLBACKTYPE"].VistaValue = flds[1];
                rec.Fields["CALLBACKPORT"].VistaValue = flds[2];
                rec.Fields["CALLBACKSERVER"].VistaValue = flds[3];
                rec.Fields["URLSTRING"].VistaValue = flds[4];
                records.Add(rec);
            }
        }
    }

    public class CallBackFileRecord
    {
        const int NFLDS = 5;

        Dictionary<string, VistaField> fields;

        public CallBackFileRecord()
        {
            setFields();
        }

        void setFields()
        {
            fields = new Dictionary<string, VistaField>(NFLDS);
            VistaField f = new VistaField();
            f.VistaName = "IEN";
            fields.Add(f.VistaName, f);

            f = new VistaField();
            f.VistaNumber = ".01";
            f.VistaName = "CALLBACKTYPE";
            f.VistaNode = "0";
            f.VistaPiece = "1";
            fields.Add(f.VistaName, f);

            f = new VistaField();
            f.VistaNumber = ".02";
            f.VistaName = "CALLBACKPORT";
            f.VistaNode = "0";
            f.VistaPiece = "2";
            fields.Add(f.VistaName, f);

            f = new VistaField();
            f.VistaNumber = ".03";
            f.VistaName = "CALLBACKSERVER";
            f.VistaNode = "0";
            f.VistaPiece = "3";
            fields.Add(f.VistaName, f);

            f = new VistaField();
            f.VistaNumber = ".04";
            f.VistaName = "URLSTRING";
            f.VistaNode = "0";
            f.VistaPiece = "4";
            fields.Add(f.VistaName, f);
        }

        public Dictionary<string, VistaField> Fields
        {
            get { return fields; }
        }
    }
}
