using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using gov.va.medora.mdo.src.mdo.dao.vista;

namespace gov.va.medora.mdo.dao
{
    interface IProceduresDao
    {
        IList<ClinicalProcedure> getClinicalProcedures();
    }
}
