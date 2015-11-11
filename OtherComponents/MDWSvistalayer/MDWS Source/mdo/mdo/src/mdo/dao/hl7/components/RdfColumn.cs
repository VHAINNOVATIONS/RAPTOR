using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo.dao.hl7.components
{
    public class RdfColumn
    {
        ColumnDescription desc;
        string val;

        public RdfColumn() { }

        public RdfColumn(ColumnDescription desc)
        {
            Description = desc;
            Value = "";
        }

        public RdfColumn(ColumnDescription desc, string value)
        {
            Description = desc;
            Value = value;
        }

        public ColumnDescription Description
        {
            get { return desc; }
            set { desc = value; }
        }

        public string Value
        {
            get { return val; }
            set { val = value; }
        }
    }
}
