using System;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.Collections;
using System.Text;
using gov.va.medora.utils;
using gov.va.medora.mdo.exceptions;
using gov.va.medora.mdo.src.mdo;

namespace gov.va.medora.mdo.dao.vista
{
    public class DdrLister : DdrQuery
    {
        bool _replacePipe = true;
        string file = "";
        string iens = "";
        string _requestedFieldString;
        string[] _requestedFields;       // Holds user's requested order
        //Hashtable requestedFieldsTbl;   // Needed to fetch by FileMan field #, and to hold if External or not
        Dictionary<String, String> _requestedFieldsTbl;
        //ArrayList ienLst;               // Needed to hold record IENs across methods.
        IList<String> _ienLst;
        string flags = "";
        string max = "";
        string from = "";
        string part = "";
        string xref = "";
        string screen = "";
        string id = "";
        string options = "";
        string moreFrom = "";
        string moreIens = "";

        public bool more = false;

        public DdrLister(AbstractConnection cxn) : base(cxn) { }

        internal MdoQuery buildRequest()
        {
            if (File == "")
            {
                throw new ArgumentNullException("Must have a file!");
            }
            VistaQuery vq = new VistaQuery("DDR LISTER");
            DictionaryHashList paramLst = new DictionaryHashList();
            paramLst.Add("\"FILE\"", File);
            if (Iens != "")
            {
                paramLst.Add("\"IENS\"", Iens);
            }
            if (_requestedFields.Length > 0)
            {
                paramLst.Add("\"FIELDS\"", getFieldsArg());
            }
            if (Flags != "")
            {
                paramLst.Add("\"FLAGS\"", Flags);
            }
            if (Max != "")
            {
                paramLst.Add("\"MAX\"", Max);
            }
            if (From != "")
            {
                paramLst.Add("\"FROM\"", From);
            }
            if (Part != "")
            {
                paramLst.Add("\"PART\"", Part);
            }
            if (Xref != "")
            {
                paramLst.Add("\"XREF\"", Xref);
            }
            if (Screen != "")
            {
                paramLst.Add("\"SCREEN\"", Screen);
            }
            if (Id != "")
            {
                paramLst.Add("\"ID\"", Id);
            }
            if (Options != "")
            {
                paramLst.Add("\"OPTIONS\"", Options);
            }
            if (moreFrom != "")
            {
                paramLst.Add("\"FROM\"", moreFrom);
            }
            if (moreIens != "")
            {
                paramLst.Add("\"IENS\"", moreIens);
            }
            vq.addParameter(vq.LIST, paramLst);
            return vq;
        }

        public string[] execute()
        {
            MdoQuery request = buildRequest();
            string response = this.execute(request);
            if (this._replacePipe)
            {
                return buildResult(response.Replace('|', '^'));
            }
            else
            {
                return buildResult(response);
            }
        }

        //public VistaRpcQuery executePlus()
        //{
        //    MdoQuery request = buildRequest();
        //    VistaRpcQuery result = this.executePlus(request);
        //    result.ParsedResult = buildResult(result.ResponseString.Replace('|', '^'));
        //    return result;
        //}

        private String getFieldsArg()
        {
            StringBuilder result = new StringBuilder();
            result.Append("@");
            for (int i = 0; i < _requestedFields.Length; i++)
            {
                if (String.Equals(_requestedFields[i], "@"))
                {
                    continue;
                }
                result.Append(';');
                result.Append(_requestedFields[i]);
            }
            return result.ToString();
        }

        private void setMoreParams(string line)
        {
            string[] flds = StringUtils.split(line, StringUtils.CARET);
            if (flds[0] != "MORE")
            {
                throw new UnexpectedDataException("Invalid return data: expected 'MORE', got " + flds[0]);
            }
            moreFrom = flds[1];
            moreIens = flds[2];
            more = true;
        }

        public string[] buildResult(string rtn)
        {
            if (String.IsNullOrEmpty(rtn))
            {
                return null;
            }

            if (this.flags.IndexOf("P") < 0) // DdrListerUtils now takes care of unpacked results so let's check if query had P flag before we split the lines unnecessarily
            {
                return DdrListerUtils.parseUnpackedResult(rtn, this.Fields, this.Id);
            }

            String[] lines = StringUtils.split(rtn, StringUtils.CRLF);
            lines = StringUtils.trimArray(lines);
            int i = 0;

            if (lines[i] == VistaConstants.MISC)
            {
                if (!lines[++i].StartsWith("MORE"))
                {
                    throw new UnexpectedDataException("Error packing LISTER return; expected 'MORE...', got " + lines[i]);
                }
                setMoreParams(lines[i]);
            }

            return parsePackedResult(lines);

            //if (this.flags.IndexOf("P") != -1)
            //{
            //    return parsePackedResult(lines);
            //}
            //else
            //{
            //    return DdrListerUtils.parseUnpackedResult(rtn, this.Fields, this.Id);
            //    //return packResult(lines);
            //}
        }

        private string[] parsePackedResult(string[] lines)
        {
            int idx = StringUtils.getIdx(lines, VistaConstants.BEGIN_ERRS, 0);
            if (idx != -1)
            {
                throw new ConnectionException(getErrMsg(lines,idx));
            }

            idx = StringUtils.getIdx(lines, VistaConstants.BEGIN_DATA, 0);
            if (idx == -1)
            {
                throw new UnexpectedDataException("Error parsing packed result: expected " + VistaConstants.BEGIN_DATA + ", found none.");
            }

            ArrayList lst = new ArrayList();
            idx++;
            while (idx < lines.Length && lines[idx] != VistaConstants.END_DATA)
            {
                lst.Add(lines[idx++]);
            }
            return (string[])lst.ToArray(typeof(string));
        }

        internal string[] packResult(string[] lines)
        {
            int idx = 0;

            if (lines[idx] == VistaConstants.UNPACKED_NO_RESULTS)
            {
                return new string[] { };
            }

            Dictionary<String, Dictionary<String, DdrField>> rs = new Dictionary<string, Dictionary<string, DdrField>>();
            Dictionary<String, String> identifierVals = new Dictionary<String, String>();

            if ((idx = StringUtils.getIdx(lines, VistaConstants.BEGIN_ERRS, 0)) != -1)
            {
                throw new ConnectionException(getErrMsg(lines,idx));
            }

            if ((idx = StringUtils.getIdx(lines, VistaConstants.BEGIN_DATA, 0)) != -1)
            {
                _ienLst = new List<String>(); // new ArrayList();
                if (lines[++idx] != VistaConstants.BEGIN_IENS)
                {
                    throw new UnexpectedDataException("Incorrectly formatted return data");
                }
                idx++;
                while (lines[idx] != VistaConstants.END_IENS)
                {
                    _ienLst.Add(lines[idx++]);
                }

                idx++;
                if (lines[idx] != VistaConstants.BEGIN_IDVALS)
                {
                    throw new UnexpectedDataException("Incorrectly formatted return data");
                }
                //String[] flds = StringUtils.split(lines[++idx], StringUtils.SEMICOLON); -- this line was wrong! see check for [MAP] index above to obtain field names
                
                IList<String> adjustedFields = new List<String>();
                foreach (String s in _requestedFields)
                {
                    if (!String.Equals("WID", s))
                    {
                        adjustedFields.Add(s);
                    }
                }

                int recIdx = 0;
                idx++;
                while (lines[idx] != VistaConstants.END_IDVALS)
                {
                    // the last field in flds is the field count, not a field <--- I don't think this is a valid comment...
                    Dictionary<String, DdrField> rec = new Dictionary<string, DdrField>();
                    //Hashtable rec = new Hashtable();
                    for (int fldIdx = 0; fldIdx < adjustedFields.Count; fldIdx++) // <--- changing this due to thinking the comment above is invalid
                    {
                        DdrField f = new DdrField();
                        f.FmNumber = _requestedFields[fldIdx];
                        String requestedOptions = (String)_requestedFieldsTbl[f.FmNumber];
                        f.HasExternal = requestedOptions.IndexOf('E') != -1;
                        if (f.HasExternal)
                        {
                            f.ExternalValue = lines[idx++];
                        }
                        if (requestedOptions.IndexOf('I') != -1)
                        {
                            f.Value = lines[idx++];
                        }
                        rec.Add(f.FmNumber, f);
                    }
                    rs.Add((String)_ienLst[recIdx++], rec);
                }


                // any identifier params? if so, turn them in to a Dictionary<String, String> where key is IEN and all lines are separated by tilde just like packed results
                if (lines.Length > (idx + 1) && String.Equals(lines[++idx], VistaConstants.BEGIN_WIDVALS))
                {
                    idx++;
                    while (!String.Equals(lines[idx], VistaConstants.END_WIDVALS))
                    {
                        String[] pieces = StringUtils.split(lines[idx], StringUtils.CARET);
                        StringBuilder sb = new StringBuilder();
                        while (!lines[++idx].StartsWith("WID") && !String.Equals(lines[idx], VistaConstants.END_WIDVALS))
                        {
                            sb.Append(lines[idx]);
                            sb.Append(StringUtils.TILDE);
                        }
                        sb.Remove(sb.Length - 1, 1);
                        identifierVals.Add(pieces[1], sb.ToString());
                    }
                }
                // at this point line should be VistaConstants.END_DATA
                // unless more functionality is added.
            }
            return toStringArray(rs, identifierVals);
        }

        private string getErrMsg(string[] lines, int idx)
        {
            string msg = lines[idx + 3];
            int endIdx = StringUtils.getIdx(lines, VistaConstants.END_ERRS, 0);
            for (int i = idx + 4; i < endIdx; i++)
            {
                if (msg[msg.Length - 1] != '.')
                {
                    msg += ". ";
                }
                msg += lines[i];
            }
            return msg;
        }

        internal String[] toStringArray(Dictionary<String, Dictionary<String, DdrField>> records, Dictionary<String, String> identifierVals)
        {
            //ArrayList lst = new ArrayList();
            IList<String> results = new List<String>();
            for (int recnum = 0; recnum < _ienLst.Count; recnum++)
            {
                StringBuilder sb = new StringBuilder();
                sb.Append(_ienLst[recnum]);
                Dictionary<String, DdrField> flds = records[_ienLst[recnum]];
                //Hashtable hashedFlds = (Hashtable)records[_ienLst[recnum]];
                for (int fldnum = 0; fldnum < _requestedFields.Length; fldnum++)
                {
                    if (String.Equals(_requestedFields[fldnum], "WID"))
                    {
                        continue;
                    }
                    String fmNum = _requestedFields[fldnum];
                    bool external = false;
                    if (fmNum.IndexOf('E') != -1)
                    {
                        fmNum = fmNum.Substring(0, fmNum.Length - 1);
                        external = true;
                    }
                    DdrField fld = (DdrField)flds[fmNum];
                    if (external)
                    {
                        sb.Append('^');
                        sb.Append(fld.ExternalValue);
                    }
                    else
                    {
                        sb.Append('^');
                        sb.Append(fld.Value);
                    }
                }
                if (identifierVals != null && identifierVals.Count > 0 && identifierVals.ContainsKey(_ienLst[recnum]))
                {
                    sb.Append("&#94;"); // packed results have this string before start of ID values
                    sb.Append(identifierVals[_ienLst[recnum]]);
                }

                results.Add(sb.ToString());
            }

            String[] final = new String[results.Count];
            results.CopyTo(final, 0);
            return final;
        }

        public String File
        {
            get { return file; }
            set { file = value; }
        }

        public String Iens
        {
            get { return iens; }
            set { iens = value; }
        }

        public String Fields
        {
            get { return _requestedFieldString; }
            set
            {
                _requestedFieldString = value;
                String s = value;
                _requestedFields = StringUtils.split(s, StringUtils.SEMICOLON);
                _requestedFieldsTbl = new Dictionary<string,string>(); // new Hashtable(_requestedFields.Length);
                for (int i = 0; i < _requestedFields.Length; i++)
                {
                    if (String.IsNullOrEmpty(_requestedFields[i]))
                    {
                        continue;
                    }
                    String fldnum = _requestedFields[i];
                    String option = "I";
                    if (fldnum.IndexOf('E') != -1)
                    {
                        fldnum = fldnum.Substring(0, fldnum.Length - 1);
                        option = "E";
                    }
                    if (!_requestedFieldsTbl.ContainsKey(fldnum))
                    {
                        _requestedFieldsTbl.Add(fldnum, option);
                    }
                    else
                    {
                        _requestedFieldsTbl[fldnum] += option;
                    }
                }
            }
        }

        public String Flags
        {
            get { return flags; }
            set { flags = value; }
        }

        public string Max
        {
            get { return max; }
            set { max = value; }
        }

        public String From
        {
            get { return from; }
            set { from = value; }
        }

        public String Part
        {
            get { return part; }
            set { part = value; }
        }

        public String Xref
        {
            get { return xref; }
            set { xref = value; }
        }

        public String Screen
        {
            get { return screen; }
            set { screen = value; }
        }

        public String Id
        {
            get { return id; }
            set { id = value; }
        }

        public String Options
        {
            get { return options; }
            set { options = value; }
        }

        public string MoreFrom
        {
            get { return moreFrom; }
            set { moreFrom = value; }
        }

        public string MoreIens
        {
            get { return moreIens; }
            set { moreIens = value; }
        }

        public bool ReplacePipe
        {
            get { return this._replacePipe; }
            set { this._replacePipe = value; }
        }
    }
}
