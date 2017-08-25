using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo.dao.hl7.components
{
    public class ColumnDescription
    {
        EncodingCharacters encChars = new EncodingCharacters();
        string fldname = "";
        string dataType = "";
        int colWidth = 0;

        public ColumnDescription() { }

        public ColumnDescription(string fldname, string dataType, int colWidth)
        {
            FieldName = fldname;
            DataType = dataType;
            ColumnWidth = colWidth;
        }

        public EncodingCharacters EncodingChars
        {
            get { return encChars; }
            set { encChars = value; }
        }

        public string FieldName
        {
            get { return fldname; }
            set { fldname = value; }
        }

        public string DataType
        {
            get { return dataType; }
            set { dataType = value; }
        }

        public int ColumnWidth
        {
            get { return colWidth; }
            set { colWidth = value; }
        }

        public string toComponent()
        {
            string result = FieldName +
                EncodingChars.ComponentSeparator + DataType +
                EncodingChars.ComponentSeparator + Convert.ToString(ColumnWidth);
            return result;
        }
    }
}
