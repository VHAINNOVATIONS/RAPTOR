using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo.dao.hl7.components
{
    public class RdtColumn
    {
        ColumnDescription desc;
        string[] values;

        public RdtColumn() { }

        public RdtColumn(ColumnDescription desc)
        {
            Description = desc;
            Values = null;
        }

        public RdtColumn(ColumnDescription desc, string value)
        {
            Description = desc;
            Values = new string[] { value };
        }

        public RdtColumn(ColumnDescription desc, string[] values)
        {
            Description = desc;
            Values = values;
        }

        public ColumnDescription Description
        {
            get { return desc; }
            set { desc = value; }
        }

        public string[] Values
        {
            get { return values; }
            set { values = value; }
        }

    }
}
