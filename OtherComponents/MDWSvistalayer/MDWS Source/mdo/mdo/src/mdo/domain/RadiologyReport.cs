using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo
{
    public class RadiologyReport : TextReport
    {
        ImagingExam _exam;
        string _accessionNumber;
        string _status;
        string _cptCode;
        string _clinicalHx;
        string _impression;

        public RadiologyReport() { }

        public ImagingExam Exam
        {
            get { return _exam; }
            set { _exam = value; }
        }
        public string AccessionNumber
        {
            get { return _accessionNumber; }
            set { _accessionNumber = value; }
        }
        public string Status
        {
            get { return _status; }
            set { _status = value; }
        }

        public string CptCode
        {
            get { return _cptCode; }
            set { _cptCode = value; }
        }

        public string ClinicalHx
        {
            get { return _clinicalHx; }
            set { _clinicalHx = value; }
        }

        public string Impression
        {
            get { return _impression; }
            set { _impression = value; }
        }

        public string getAccessionNumber(string vistaProcedureDateTime, string caseNumber, utils.DateFormat dateFormat)
        {
            if(String.IsNullOrEmpty(caseNumber))
            {
                throw new exceptions.MdoException(exceptions.MdoExceptionCode.ARGUMENT_NULL, "Need to supply the case number");
            }

            if (dateFormat == utils.DateFormat.VISTA)
            {
                if (String.IsNullOrEmpty(vistaProcedureDateTime) || vistaProcedureDateTime.Length < 7)
                {
                    throw new exceptions.MdoException(exceptions.MdoExceptionCode.ARGUMENT_DATE_FORMAT, "Expected a Vista formate timestamp (e.g. 3011225 or 3011225.120005)");
                }
                return vistaProcedureDateTime.Substring(3, 2) +
                    vistaProcedureDateTime.Substring(5, 2) + vistaProcedureDateTime.Substring(1, 2) + "-" + caseNumber;
            }
            if (dateFormat == utils.DateFormat.ISO)
            {
                if (String.IsNullOrEmpty(vistaProcedureDateTime) || vistaProcedureDateTime.Length < 8)
                {
                    throw new exceptions.MdoException(exceptions.MdoExceptionCode.ARGUMENT_DATE_FORMAT, "Expected a ISO formate timestamp (e.g. 20101225)");

                }
                return vistaProcedureDateTime.Substring(4, 2) + vistaProcedureDateTime.Substring(6, 2) +
                    vistaProcedureDateTime.Substring(2, 2) + "-" + caseNumber;
            }
            if (dateFormat == utils.DateFormat.UTC)
            {
                throw new NotSupportedException("UTC format is not currently supported");
            }
            // shouldn't reach here since all enums are accounted for but IDE complains if left out
            return null;
        }
    }
}
