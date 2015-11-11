using System;
using System.Collections.Generic;
using System.Collections;
using gov.va.medora.utils;
using gov.va.medora.mdo.dao.hl7.components;

namespace gov.va.medora.mdo.dao.hl7.segments
{
    public class VtqSegment
    {
        EncodingCharacters encChars = new EncodingCharacters();
        string queryTag = "";
        string formatCode = "";
        string queryName = "";
        string virtualTableName = "";
        ArrayList selectionCriteria;

        public VtqSegment() { }

        public EncodingCharacters EncodingChars
        {
            get { return encChars; }
            set { encChars = value; }
        }

        public string QueryTag
        {
            get { return queryTag; }
            set { queryTag = value; }
        }

        public string FormatCode
        {
            get { return formatCode; }
            set { formatCode = value; }
        }

        public string QueryName
        {
            get { return queryName; }
            set { queryName = value; }
        }

        public string VirtualTableName
        {
            get { return virtualTableName; }
            set { virtualTableName = value; }
        }

        public ArrayList SelectionCriteria
        {
            get { return selectionCriteria; }
            set { selectionCriteria = value; }
        }

        public string toSegment()
        {
            string result = "VTQ" +
                EncodingChars.FieldSeparator + QueryTag +
                EncodingChars.FieldSeparator + FormatCode +
                EncodingChars.FieldSeparator + QueryName +
                EncodingChars.FieldSeparator + VirtualTableName +
                EncodingChars.FieldSeparator;
            for (int i = 0; i < SelectionCriteria.Count; i++)
            {
                result += ((SelectionCriterion)SelectionCriteria[i]).toComponent();
            }
            return result + '\r';
        }
    }
}
