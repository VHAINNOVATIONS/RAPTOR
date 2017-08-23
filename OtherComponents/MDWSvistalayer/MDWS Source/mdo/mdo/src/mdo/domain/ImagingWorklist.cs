using System;
using System.Collections.Generic;
using System.Collections;
using System.Text;

namespace gov.va.medora.mdo
{
    public class ImagingWorklist
    {
        string timestamp;
        ArrayList exams;

        public ImagingWorklist() { }

        public string Timestamp
        {
            get { return timestamp; }
            set { timestamp = value; }
        }

        public ImagingExam[] Exams
        {
            get
            {
                if (exams == null)
                {
                    return null;
                }
                return (ImagingExam[])exams.ToArray(typeof(ImagingExam));
            }
        }

        public void add(ImagingExam exam)
        {
            exams.Add(exam);
        }
    }
}
