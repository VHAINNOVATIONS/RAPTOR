using System;
using System.Collections;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.Xml;
using System.Reflection;
using gov.va.medora.utils;

namespace gov.va.medora.mdo.dao.vista
{
    public class VistaSystemFileHandler : ISystemFileHandler
    {
        AbstractConnection myCxn;

        Hashtable fileDefs;
        Hashtable files;
        Hashtable lookupTables;

        public VistaSystemFileHandler(AbstractConnection cxn)
        {
            myCxn = cxn;
            getFileDefs();
            files = new Hashtable();
            lookupTables = new Hashtable();
        }

        public Dictionary<string, object> getFile(string fileNum)
        {
            if (!files.ContainsKey(fileNum))
            {
                VistaFile theFile = (VistaFile)fileDefs[fileNum];
                DdrLister query = buildFileQuery(theFile);
                string[] response = query.execute();
                files.Add(fileNum, toMdo(response, theFile));
            }
            return (Dictionary<string, object>)files[fileNum];
        }

        public StringDictionary getLookupTable(string fileNum)
        {
            if (!lookupTables.ContainsKey(fileNum))
            {
                DdrLister query = buildIenNameQuery(fileNum);
                string[] response = query.execute();
                lookupTables.Add(fileNum, VistaUtils.toStringDictionary(response));
            }
            return (StringDictionary)lookupTables[fileNum];
        }

        internal DdrLister buildFileQuery(VistaFile file)
        {
            DdrLister query = new DdrLister(myCxn);
            query.File = file.FileNumber;
            //query.Fields = file.getFieldString();
            query.Flags = "IP";
            query.Xref = "#";
            return query;
        }

        internal void getFileDefs()
        {
            VistaFile currentFile = null;
            VistaField currentFld = null;
            fileDefs = new Hashtable();

            XmlReader reader = new XmlTextReader(VistaConstants.VISTA_FILEDEFS_PATH);
            while (reader.Read())
            {
                switch ((int)reader.NodeType)
                {
                    case (int)XmlNodeType.Element:
                        string name = reader.Name;
                        if (name == "File")
                        {
                            currentFile = new VistaFile();
                            currentFile.FileName = reader.GetAttribute("name");
                            currentFile.FileNumber = reader.GetAttribute("number");
                            currentFile.Global = reader.GetAttribute("global");
                            currentFile.MdoName = reader.GetAttribute("mdo");
                        }
                        else if (name == "fields")
                        {
                           // currentFile.Fields = new DictionaryHashList();
                        }
                        else if (name == "field")
                        {
                            currentFld = new VistaField();
                            currentFld.Pos = Convert.ToInt16(reader.GetAttribute("pos"));
                        }
                        else if (name == "vista")
                        {
                            currentFld.VistaName = reader.GetAttribute("name");
                            currentFld.VistaNumber = reader.GetAttribute("number");
                            currentFld.VistaNode = reader.GetAttribute("node");
                            currentFld.VistaPiece = reader.GetAttribute("piece");
                            //currentFile.Fields.Add(currentFld.Pos.ToString(), currentFld);
                        }
                        else if (name == "mdo")
                        {
                            currentFld.MdoName = reader.GetAttribute("name");
                            currentFld.MdoType = reader.GetAttribute("type");
                        }
                        else if (name == "mapping")
                        {
                            string mappingType = reader.GetAttribute("type");
                            currentFld.Mapping = new VistaFieldMapping(mappingType);
                            if (currentFld.Mapping.Type == "pointer")
                            {
                                currentFld.Mapping.VistaFileNumber = reader.GetAttribute("file");
                            }
                            else if (currentFld.Mapping.Type == "decode")
                            {
                                //currentFld.Mapping.DecodeMap = new StringDictionary();
                            }
                        }
                        else if (name == "map")
                        {
                            //currentFld.Mapping.DecodeMap.Add(reader.GetAttribute("code"), reader.GetAttribute("value"));
                        }
                        break;
                    case (int)XmlNodeType.EndElement:
                        name = reader.Name;
                        if (name == "File")
                        {
                            fileDefs.Add(currentFile.FileNumber, currentFile);
                        }
                        else if (name == "fields")
                        {
                        }
                        else if (name == "field")
                        {
                        }
                        else if (name == "vista")
                        {
                        }
                        else if (name == "mdo")
                        {
                        }
                        else if (name == "mapping")
                        {
                        }
                        else if (name == "map")
                        {
                        }
                        break;
                }
            }
        }

        internal DdrLister buildIenNameQuery(string fileNum)
        {
            DdrLister query = new DdrLister(myCxn);
            query.File = fileNum;
            query.Fields = ".01";
            query.Flags = "IP";
            query.Xref = "#";
            return query;
        }

        internal Dictionary<string, object> toMdo(string[] response, VistaFile theFile)
        {
            if (response == null || response.Length == 0)
            {
                return null;
            }
            Dictionary<string, object> result = new Dictionary<string, object>(response.Length);
            for (int lineIdx = 0; lineIdx < response.Length; lineIdx++)
            {
                //Need to instantiate the mdo here
                Object theMdo = Activator.CreateInstance(Type.GetType(theFile.MdoName), new object[] { });
                Type theClass = theMdo.GetType();
                string[] flds = StringUtils.split(response[lineIdx], StringUtils.CARET);
                for (int fldIdx = 0; fldIdx < flds.Length; fldIdx++)
                {
                    VistaField vf = null; // (VistaField)((DictionaryEntry)theFile.Fields[fldIdx]).Value;
                    FieldInfo theField = theClass.GetField(vf.MdoName, BindingFlags.NonPublic | BindingFlags.Instance);
                    if (vf.MdoType == "string")
                    {
                        if (vf.Mapping != null && vf.Mapping.Type == "decode")
                        {
                           // if (vf.Mapping.DecodeMap.ContainsKey(flds[fldIdx]))
                          //  {
                                //theField.SetValue(theMdo, vf.Mapping.DecodeMap[flds[fldIdx]]);
                          //  }
                         //   else
                          //  {
                                theField.SetValue(theMdo, flds[fldIdx]);
                         //   }
                        }
                        else
                        {
                            theField.SetValue(theMdo, flds[fldIdx]);
                        }
                    }
                    else if (vf.MdoType == "kvp")
                    {
                        string key = flds[fldIdx];
                        string value = "";
                        if (vf.Mapping != null && vf.Mapping.Type == "decode")
                        {
                           // value = vf.Mapping.DecodeMap[key];
                        }
                        else
                        {
                            StringDictionary lookupTbl = getLookupTable(vf.Mapping.VistaFileNumber);
                            if (lookupTbl.ContainsKey(key))
                            {
                                value = lookupTbl[key];
                            }
                        }
                        theField.SetValue(theMdo, new KeyValuePair<string, string>(key, value));
                        //KeyValuePair<string, string> kvp = new KeyValuePair<string, string>(key, value);
                    }
                }
                result.Add(flds[0], theMdo);
            }
            return result;
        }

        public Hashtable LookupTables()
        {
            return lookupTables;
        }

        public Hashtable Files()
        {
            return files;
        }
    }
}
