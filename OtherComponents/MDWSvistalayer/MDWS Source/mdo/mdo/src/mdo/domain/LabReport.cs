using System;
using System.Collections.Generic;
using System.Collections;
using System.Text;

namespace gov.va.medora.mdo
{
    public class LabReport : Report
    {
        public LabPanel Panel { get; set; }
        //IList<LabTest> _tests;
        LabResult _result;
        LabSpecimen specimen;
        string comment;

        public LabReport() { }

        //public IList<LabTest> Tests
        //{
        //    get { return _tests; }
        //    set { _tests = value; }
        //}

        public LabResult Result
        {
            get { return _result; }
            set { _result = value; }
        }

        public LabSpecimen Specimen
        {
            get { return specimen; }
            set { specimen = value; }
        }

        public string Comment
        {
            get { return comment; }
            set { comment = value; }
        }

    }
}
