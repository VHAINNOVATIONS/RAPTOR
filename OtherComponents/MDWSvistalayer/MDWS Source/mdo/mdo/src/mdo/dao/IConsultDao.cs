using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo.dao
{
    public interface IConsultDao
    {
        string getConsultNote(string consultId);
        Consult[] getConsultsForPatient();
        Consult[] getConsultsForPatient(string pid);
    }
}
