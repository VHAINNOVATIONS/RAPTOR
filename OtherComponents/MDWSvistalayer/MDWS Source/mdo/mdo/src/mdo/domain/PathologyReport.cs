using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo
{
    public class PathologyReport : LabReport
    {
        string clinicalHx;
        string description;
        string exam;
        string diagnosis;

        public PathologyReport() 
        {
            ClinicalHx = "";
            Description = "";
            Exam = "";
            Diagnosis = "";
        }

        public string ClinicalHx
        {
            get { return clinicalHx; }
            set { clinicalHx = value; }
        }

        public string Description
        {
            get { return description; }
            set { description = value; }
        }

        public string Exam
        {
            get { return exam; }
            set { exam = value; }
        }

        public string Diagnosis
        {
            get { return diagnosis; }
            set { diagnosis = value; }
        }

    }
}
