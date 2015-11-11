using System;
using System.Collections.Specialized;
using System.Text;

namespace gov.va.medora.mdo.dao.vista
{
    public class VistaFieldMapping
    {
        string type;
        string vistaFileNum;
       // StringDictionary decodeMap;

        public VistaFieldMapping() { }

        public VistaFieldMapping(string type)
        {
            Type = type;
        }

        public string Type
        {
            get { return type; }
            set { type = value; }
        }

        public string VistaFileNumber
        {
            get { return vistaFileNum; }
            set { vistaFileNum = value; }
        }

        //public StringDictionary DecodeMap
        //{
        //    get { return decodeMap; }
        //    set { decodeMap = value; }
        //}
    }
}
