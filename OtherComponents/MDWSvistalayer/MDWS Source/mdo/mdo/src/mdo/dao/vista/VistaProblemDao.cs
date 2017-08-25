using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using gov.va.medora.mdo.dao;
using gov.va.medora.utils;

namespace gov.va.medora.mdo.dao.vista
{
    public class VistaProblemDao : gov.va.medora.mdo.dao.IProblemDao
    {
        VistaConnection _cxn;

        public VistaProblemDao(AbstractConnection cxn)
        {
            _cxn = (VistaConnection)cxn;
        }

        public IList<Problem> getProblems(String status)
        {
            return getProblems(_cxn.Pid);
        }

        internal IList<Problem> getProblems(String status, String pid)
        {
            MdoQuery request = buildGetProblemsRequest(status, pid);
            String response = (String)_cxn.query(request);
            return toProblems(response);
        }

        internal MdoQuery buildGetProblemsRequest(String status, String pid)
        {
            VistaQuery vq = new VistaQuery("ORQQPL PROBLEM LIST");
            vq.addParameter(vq.LITERAL, pid);
            if (String.IsNullOrEmpty(status))
            {
                status = "B";
            }
            vq.addParameter(vq.LITERAL, status);
            return vq;
        }

        internal IList<Problem> toProblems(String response)
        {
            IList<Problem> result = new List<Problem>();

            if (String.IsNullOrEmpty(response))
            {
                return result;
            }

            String[] lines = StringUtils.split(response, StringUtils.CRLF);

            for (int i = 0; i < lines.Length; i++)
            {
                String[] pieces = StringUtils.split(lines[i], StringUtils.CARET);
                if (pieces.Length < 13)
                {
                    continue;
                }

                Problem problem = new Problem();
                problem.Id = pieces[0];
                problem.Status = pieces[1];
                problem.Type = new ObservationType() { Name = pieces[2] };
                problem.Icd = pieces[3];
                problem.OnsetDate = pieces[4];
                problem.ModifiedDate = pieces[5];
                problem.IsServiceConnected = String.Equals("SC", pieces[6], StringComparison.CurrentCultureIgnoreCase);
                String[] authorPieces = StringUtils.split(pieces[11], StringUtils.SEMICOLON);
                if (authorPieces.Length > 1)
                {
                    problem.Recorder = new Author(authorPieces[0], authorPieces[1], "");
                }
                String[] servicePieces = StringUtils.split(pieces[12], StringUtils.SEMICOLON);
                if (servicePieces.Length > 1)
                {
                    problem.Location = new HospitalLocation(servicePieces[0], servicePieces[1]);
                }

                result.Add(problem);
            }


            return result;
        }
 
    }
}
