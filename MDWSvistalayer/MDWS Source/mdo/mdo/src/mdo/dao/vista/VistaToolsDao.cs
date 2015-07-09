using System;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.Collections;
using System.Text;
using gov.va.medora.utils;
using gov.va.medora.mdo.src.mdo;
using gov.va.medora.mdo.exceptions;

namespace gov.va.medora.mdo.dao.vista
{
    /// <summary>
    /// 
    /// </summary>
    /// <remarks>
    /// for more information on underlying calls, see http://www.hardhats.org/fileman/pm/dba_frm.htm
    /// </remarks>
    public class VistaToolsDao : IToolsDao
    {
        AbstractConnection cxn = null;

        public VistaToolsDao(AbstractConnection cxn)
        {
            this.cxn = cxn;
        }

        public string[] ddrLister(
            string file,
            string iens,
            string flds,
            string flags,
            string maxRex,
            string from,
            string part,
            string xref,
            string screen,
            string identifier
            )
        {
            DdrLister query = new DdrLister(cxn);
            query.File = file;
            query.Iens = iens;
            query.Fields = flds;
            query.Flags = flags;
            query.Max = maxRex;
            query.From = from;
            query.Part = part;
            query.Xref = xref;
            query.Screen = screen;
            query.Id = identifier;
            String[] rtn = query.execute();
            return rtn;
        }

        public string getFieldAttribute(string file, string fld, string attribute)
        {
            string arg = "$P($$GET1^DID(\"" + file + "\",\"" + fld + "\",\"\",\"" + attribute + "\"),U,1)";
            string rtn = VistaUtils.getVariableValue(cxn,arg);
            return rtn;
        }

        public string ddiol(string file, string fld, string attribute)
        {
            string arg = "$P(D EN^DDIOL($$GET1^DID(\"9000010.23\",\".02\",\"\",\"LABEL\")),U,1)";
            string rtn = VistaUtils.getVariableValue(cxn,arg);
            return rtn;
        }

        public string[] ddrGetsEntry(
            string file,
            string iens,
            string flds,
            string flags)
        {
            DdrGetsEntry query = new DdrGetsEntry(cxn);
            query.File = file;
            query.Iens = iens;
            query.Fields = flds;
            query.Flags = flags;
            string[] result = query.execute();
            return result;
        }

        public string getVariableValue(string arg)
        {
            return VistaUtils.getVariableValue(cxn,arg);
        }

        public KeyValuePair<string,string>[] getRpcList(string target)
        {
            MdoQuery request = buildGetRpcListRequest(target);
            string response = (string)cxn.query(request);
            return toRpcArray(response);
        }

        internal MdoQuery buildGetRpcListRequest(string target)
        {
            VistaQuery vq = new VistaQuery("XWB RPC LIST");
            vq.addParameter(vq.LITERAL, target);
            return vq;
        }

        internal KeyValuePair<string,string>[] toRpcArray(string response)
        {
            if (response == "")
            {
                return null;
            }
            string[] lines = StringUtils.split(response, StringUtils.CRLF);
            lines = StringUtils.trimArray(lines);
            KeyValuePair<string, string>[] result = new KeyValuePair<string,string>[lines.Length];
            for (int i = 0; i < lines.Length; i++)
            {
                int p = lines[i].IndexOf(' ');
                string IEN = lines[i].Substring(0, p);
                string name = getRpcName(IEN);
                result[i] = new KeyValuePair<string, string>(IEN, name);
            }
            return result;
        }

        public string getRpcName(string rpcIEN)
        {
            string arg = "$P($G(^XWB(8994," + rpcIEN + ",0)),U,1)";
            string response = VistaUtils.getVariableValue(cxn,arg);
            return response;
        }

        public bool isRpcAvailableAtSite(string target, string localRemote, string version)
        {
            MdoQuery request = buildIsRpcAvailableAtSiteRequest(target, localRemote, version);
            string response = (string)cxn.query(request);
            return response == "1";
        }

        internal MdoQuery buildIsRpcAvailableAtSiteRequest(string target, string localRemote, string version)
        {
            localRemote = localRemote.ToUpper();
            if (localRemote != "R" && localRemote != "L" && localRemote != "")
            {
                throw new Exception("Invalid localRemote param, must be empty, R or L");
            }
            VistaQuery vq = new VistaQuery("XWB IS RPC AVAILABLE");
            vq.addParameter(vq.LITERAL, target);
            vq.addParameter(vq.LITERAL, localRemote);
            vq.addParameter(vq.LITERAL, (version == "" ? "0" : version));
            return vq;
        }

        public string isRpcAvailable(string target, string context)
        {
            return isRpcAvailable(target, context, "L", "");
        }

        public string isRpcAvailable(string target, string context, string localRemote, string version)
        {
            if (!isRpcAvailableAtSite(target, localRemote, version))
            {
                return "Not installed at site";
            }
            KeyValuePair<string,string>[] rpcList = getRpcList(target);
            string rpcIEN = rpcList[0].Key;
            VistaUserDao userDao = new VistaUserDao(cxn);
            string optIEN = userDao.getOptionIen(context);
            if (!StringUtils.isNumeric(optIEN))
            {
                return "Error getting context IEN: " + optIEN;
            }
            DdrLister query = buildGetOptionRpcsQuery(optIEN);
            string[] optRpcs = query.execute();
            if (!isRpcIenPresent(optRpcs, rpcIEN))
            {
                return "RPC not in context";
            }
            return "YES";
        }

        internal DdrLister buildGetOptionRpcsQuery(string optIEN)
        {
            DdrLister query = new DdrLister(cxn);
            query.File = "19.05";
            query.Iens = "," + optIEN + ",";
            query.Fields = ".01";
            query.Flags = "IP";
            return query;
        }

        internal bool isRpcIenPresent(string[] optRpcs, string rpcIEN)
        {
            if (optRpcs == null)
            {
                return false;
            }
            for (int i = 0; i < optRpcs.Length; i++)
            {
                string[] flds = StringUtils.split(optRpcs[i], StringUtils.CARET);
                if (flds[1] == rpcIEN)
                {
                    return true;
                }
            }
            return false;
        }

        public string xusHash(string s)
        {
            DdrLister query = buildXusHashQuery(s);
            string[] response = query.execute();
            return StringUtils.piece(response[0],StringUtils.CARET,2);
        }

        internal DdrLister buildXusHashQuery(string s)
        {
            DdrLister query = new DdrLister(cxn);
            query.File = "200";
            query.Flags = "IP";
            query.Fields = "";
            query.Max = "1";
            query.Id = "S X=$$EN^XUSHSH(\"" + s + "\") D EN^DDIOL(X)";
            return query;
        }

        public FileHeader getFileHeader(string globalName)
        {
            string arg = "$G(" + globalName + "0))";
            string response = VistaUtils.getVariableValue(cxn, arg);
            return toFileHeader(response);
        }

        internal FileHeader toFileHeader(string response)
        {
            if (response == "")
            {
                return null;
            }
            FileHeader result = new FileHeader();
            string[] flds = StringUtils.split(response, StringUtils.CARET);
            result.Name = flds[0];
            result.AlternateName = "";
            int i = 0;
            while (StringUtils.isNumericChar(flds[1][i]))
            {
                result.AlternateName += flds[1][i];
                i++;
            }
            if (i < flds[1].Length)
            {
                result.Characteristics = new ArrayList();
                do
                {
                    result.Characteristics.Add(flds[1][i]);
                    i++;
                } while (i < flds[1].Length);
            }
            result.LatestId = flds[2];
            result.NumberOfRecords = Convert.ToInt64(flds[3]);
            return result;
        }

        public string getTimestamp()
        {
            MdoQuery request = buildGetTimestampRequest();
            string response = (string)cxn.query(request);
            return VistaTimestamp.toUtcString(response);
        }

        internal MdoQuery buildGetTimestampRequest()
        {
            VistaQuery vq = new VistaQuery("ORWU DT");
            vq.addParameter(vq.LITERAL, "NOW");
            return vq;
        }

        public string runRpc(string rpcName, string[] paramValues, int[] paramTypes, bool[] paramEncrypted)
        {

            MdoQuery request = buildRpcRequest(rpcName, paramValues, paramTypes, paramEncrypted);
            string response = (string)cxn.query(request);
            return response;
        }

        public MdoQuery buildRpcRequest(string rpcName, string[] paramValues, int[] paramTypes, bool[] paramEncrypted)
        {
            if (String.IsNullOrEmpty(rpcName))
            {
                throw new MdoException(MdoExceptionCode.ARGUMENT_INVALID, "rpcName must be specified");
            }
            if (paramValues.Length != paramTypes.Length || paramValues.Length != paramEncrypted.Length)
            {
                throw new MdoException(MdoExceptionCode.ARGUMENT_INVALID, "paramValues, paramTypes and paramEncrpted must be the same size");
            }

            VistaQuery vq = new VistaQuery(rpcName);
            for (int n = 0; n < paramValues.Length; n++)
            {
                if (paramEncrypted[n])
                {
                    vq.addEncryptedParameter(paramTypes[n], paramValues[n]);
                }
                else
                {
                    vq.addParameter(paramTypes[n], paramValues[n]);
                }
            }
            return vq;
        }

        public byte runQueryThread()
        {
            // this function serves to do nothing more than serve as a tool to initialize a query thread. 
            // Afaik, a byte is the smallest object available in the .NET runtime 
            return new byte();
        }

        /// <summary>
        /// Get a VistA file - details include file name and globoal as well as all VistA fields (each field has detailed info such as name, number, transforms and type).
        /// 
        /// This call can be long running depending on the size of the file (the PATIENT file contains over 500 fields which currently means over 500 RPCs
        /// will be executed to retrieve all the information - this file can take nearly a minute at an average site)
        /// </summary>
        /// <param name="fileNumber">The Vista file number (e.g. "2")</param>
        /// <returns>VistaFile</returns>
        public VistaFile getFile(string vistaNumber)
        {
            return getFile(vistaNumber, false); // don't get XRefs by default as it can be unintentionally long running
        }

        /// <summary>
        /// Get a VistA file - details include file name and globoal as well as all VistA fields (each field has detailed info such as name, number, transforms and type).
        /// 
        /// This call can be long running depending on the size of the file (the PATIENT file contains over 500 fields which currently means over 500 RPCs
        /// will be executed to retrieve all the information - this file can take nearly a minute at an average site)
        /// </summary>
        /// <param name="fileNumber">The Vista file number (e.g. "2")</param>
        /// <param name="includeXRefs">If true, fetches cross refs for files</param>
        /// <returns>VistaFile</returns>
        public VistaFile getFile(string fileNumber, bool includeXRefs)
        {
            VistaFile result = new VistaFile() { FileNumber = fileNumber };

            string fileNameQuery = String.Format("$P($G(^DIC({0},0)),U,1)", fileNumber);
            string fileGlobalQuery = String.Format("$G(^DIC({0},0,\"GL\"))", fileNumber);
            string combinedQueries = new StringBuilder().Append(fileNameQuery).Append("_\"|^|\"_").Append(fileGlobalQuery).ToString();
            string combinedResult = getVariableValue(combinedQueries);
            string[] pieces = StringUtils.split(combinedResult, "|^|");
            result.FileName = pieces[0];
            result.Global = pieces[1];
            result.FieldsDict = getFields(result);

            if (includeXRefs)
            {
                Dictionary<string, CrossRef> xrefs = getXRefs(result);
                result.XRefs = new List<CrossRef>();
                foreach (CrossRef xref in xrefs.Values)
                {
                    result.XRefs.Add(xref);
                }
            }

            return result;
        }

        public Dictionary<string, VistaField> getFields(VistaFile file)
        {
            Dictionary<string, VistaField> result = new Dictionary<string, VistaField>();

            string currentFieldName = getVariableValue(String.Format("$O(^DD({0},\"B\",\"{1}\"))", file.FileNumber, ""));
            while (!String.IsNullOrEmpty(currentFieldName))
            {
                VistaField currentField = getField(file, currentFieldName, ref currentFieldName); // passed by reference because RPC below fetches next field name for loop
                if (!result.ContainsKey(currentField.VistaNumber))
                {
                    result.Add(currentField.VistaNumber, currentField);
                }

                //currentFieldName = getVariableValue(String.Format("$O(^DD({0},\"B\",\"{1}\"))", file.FileNumber, currentFieldName));
            }

            return result;
        }

        internal VistaField getField(VistaFile file, string fieldName, ref string nextField)
        {
            VistaField result = new VistaField();
            result.VistaName = fieldName;

            // get next field, get field number and get field props with one combined query delimited with the |^| string
            string combinedQuery = String.Format("$O(^DD({0},\"B\",\"{1}\"))_\"|^|\"_$O(^DD({0},\"B\",\"{1}\",0))_\"|^|\"_$G(^DD({0},$O(^DD({0},\"B\",\"{1}\",0)),0))", file.FileNumber, fieldName);
            string combinedResults = getVariableValue(combinedQuery);
            string[] pieces = StringUtils.split(combinedResults, "|^|");

            nextField = pieces[0];
            result.VistaNumber = pieces[1];
            string fieldProps = pieces[2];
            //result.VistaNumber = getVariableValue(String.Format("$O(^DD({0},\"B\",\"{1}\",0))", file.FileNumber, fieldName));
            //string fieldProps = getVariableValue(String.Format("$G(^DD({0},{1},0))", file.FileNumber, result.VistaNumber));
            string[] propPieces = StringUtils.split(fieldProps, StringUtils.CARET);
            if (propPieces.Length < 4)
            {
                return result;
            }
            result.Type = propPieces[1];
            result.VistaNode = String.Concat("(", propPieces[3], ")");

            if (propPieces.Length > 2 && !String.IsNullOrEmpty(propPieces[1]))
            {
                if (propPieces[1].Contains("P") && !String.IsNullOrEmpty(propPieces[2]))
                {
                    result.IsPointer = true;
                    string pointedToFileHeader = getVariableValue(String.Format("$G(^{0}0))", propPieces[2])); // need to put carat back in because string split removes it
                    string[] pointedToFileHeaderPieces = StringUtils.split(pointedToFileHeader, StringUtils.CARET);
                    result.PointsTo = new VistaFile() 
                    {
                        Global = "^" + propPieces[2], // need to put carat back in because string split removes it
                        FileName = pointedToFileHeaderPieces[0],
                        FileNumber = pointedToFileHeaderPieces[1]
                    };
                }
                else if (String.Equals(propPieces[1], "S", StringComparison.CurrentCultureIgnoreCase))
                {
                    if (!String.IsNullOrEmpty(propPieces[2]))
                    {
                        result.Externals = new Dictionary<string, string>();
                        String[] externalsKeysAndVals = propPieces[2].Split(new String[] { ";" }, StringSplitOptions.RemoveEmptyEntries);
                        foreach (String individual in externalsKeysAndVals)
                        {
                            String[] keyAndVal = individual.Split(new String[] { ":" }, StringSplitOptions.RemoveEmptyEntries);
                            if (!result.Externals.ContainsKey(keyAndVal[0]))
                            {
                                result.Externals.Add(keyAndVal[0], keyAndVal[1]);
                            }
                            else
                            {
                                System.Console.WriteLine("Found dupe: " + keyAndVal[0] + " - " + keyAndVal[1] + " which was a duplicate of: " + keyAndVal[0] + ":" + result.Externals[keyAndVal[0]]);
                            }
                        }
                    }
                }
            }
            if (propPieces.Length == 4) // multiple or WP - Need to do some work figuring out what all these characters mean - here??? -> http://www.hardhats.org/fileman/u2/fa_cond.htm
            {
                decimal trash = 0;
                if (Decimal.TryParse(propPieces[1], out trash))
                {
                    result.IsMultiple = true;
                    result.Multiple = new VistaFile() { FileNumber = propPieces[1] };
                    //result.Multiple.FieldsDict = getFields(result.Multiple);
                }
                else
                {
                    result.IsWordProc = true;
                }
            }

            // re-assemble transform
            if (propPieces.Length > 4)
            {
                StringBuilder sb = new StringBuilder();
                for (int i = 4; i < propPieces.Length; i++)
                {
                    sb.Append(propPieces[i]);
                    sb.Append("^");
                }
                sb = sb.Remove(sb.Length - 1, 1);
                result.Transform = sb.ToString();
            }

            return result;
        }

        public Dictionary<string, CrossRef> getXRefs(VistaFile file)
        {
            Dictionary<string, CrossRef> result = new Dictionary<string, CrossRef>();

            string currentXRefName = getVariableValue(String.Format("$O(^DD({0},0,\"IX\",\"\"))", file.FileNumber));
            while (!String.IsNullOrEmpty(currentXRefName))
            {
                if (!result.ContainsKey(currentXRefName))
                {
                    result.Add(currentXRefName, new CrossRef() { Name = currentXRefName });
                }

                // cut number of queries by 3 for each pass - 1/4 as many RPC calls to Vista
                string combinedQuery = String.Format("$O(^DD({0},0,\"IX\",\"{1}\"))_\"|^|\"_" + // get next xref name
                    "$O(^DD({0},0,\"IX\",\"{1}\",\"\"))_\"|^|\"_" + // get DD
                    "$O(^DD({0},0,\"IX\",\"{1}\",$O(^DD({0},0,\"IX\",\"{1}\",\"\")),\"\"))_\"|^|\"_" + // get field number
                    "$P($G(^DD($O(^DD({0},0,\"IX\",\"{1}\",\"\")),$O(^DD({0},0,\"IX\",\"{1}\",$O(^DD({0},0,\"IX\",\"{1}\",\"\")),\"\")),0)),U,1)", file.FileNumber, currentXRefName); // get fieldName
                //string dd = getVariableValue(String.Format("$O(^DD({0},0,\"IX\",\"{1}\",\"\"))", file.FileNumber, currentXRefName));
                //string fieldNumber = getVariableValue(String.Format("$O(^DD({0},0,\"IX\",\"{1}\",\"{2}\",\"\"))", file.FileNumber, currentXRefName, dd));
                //string fieldName = getVariableValue(String.Format("$P($G(^DD(\"{0}\",\"{1}\",0)),U,1)", dd, fieldNumber));
                string combinedResults = getVariableValue(combinedQuery);
                string[] pieces = StringUtils.split(combinedResults, "|^|");
                string dd = pieces[1];
                string fieldNumber = pieces[2];
                string fieldName = pieces[3];

                result[currentXRefName].FieldNumber = fieldNumber;
                result[currentXRefName].FieldName = fieldName;
                result[currentXRefName].DD = dd;

                currentXRefName = pieces[0];
                //currentXRefName = getVariableValue(String.Format("$O(^DD({0},0,\"IX\",\"{1}\"))", file.FileNumber, currentXRefName));
            }

            return result;
        }
        	
    }
}
