using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.utils;
using gov.va.medora.mdo.dao.hl7.components;

namespace gov.va.medora.mdo.dao.hl7.segments
{
    public class RdtSegment
    {
        EncodingCharacters encChars = new EncodingCharacters();
        RdtColumn[] columns;

        public RdtSegment() { }

        public RdtSegment(RdfSegment rdfSeg, string rawSeg)
        {
            parse(rdfSeg, rawSeg);
        }

        public EncodingCharacters EncodingChars
        {
            get { return encChars; }
            set { encChars = value; }
        }

        public RdtColumn[] Columns
        {
            get { return columns; }
            set { columns = value; }
        }

        void parse(RdfSegment rdfSeg, string rawSeg)
        {
            string[] flds = StringUtils.split(rawSeg, EncodingChars.FieldSeparator);

            if (flds.Length != rdfSeg.Columns.Length + 1)
            {
                throw new Exception("Invalid RDT segment: incorrect number of columns");
            }

            if (flds[0] != "RDT")
            {
                throw new Exception("Invalid RDT segment: incorrect header");
            }

            columns = new RdtColumn[rdfSeg.Columns.Length];
            for (int i = 0; i < rdfSeg.Columns.Length; i++)
            {
                string[] columnValues = getColumnValues(flds[i+1]);
                if (columnValues == null || columnValues.Length == 0)
                {
                    columns[i] = new RdtColumn(rdfSeg.Columns[i].Description);
                }
                else if (columnValues.Length == 1)
                {
                    columns[i] = new RdtColumn(rdfSeg.Columns[i].Description, columnValues[0]);
                }
                else
                {
                    columns[i] = new RdtColumn(rdfSeg.Columns[i].Description, columnValues);
                }
            }
        }

        string[] getColumnValues(string rawColumn)
        {
            if (rawColumn == null || rawColumn.Trim() == "")
            {
                return null;
            }
            string[] result = StringUtils.split(rawColumn, EncodingChars.RepetitionSeparator);
            return result;
        }


        public RdtColumn getColumn(string colName)
        {
            for (int i = 0; i < Columns.Length; i++)
            {
                if (Columns[i].Description.FieldName == colName)
                {
                    return Columns[i];
                }
            }
            return null;
        }
    }
}
