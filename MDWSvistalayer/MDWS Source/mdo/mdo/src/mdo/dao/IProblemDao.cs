using System;

namespace gov.va.medora.mdo.dao
{
    public interface IProblemDao
    {
        System.Collections.Generic.IList<gov.va.medora.mdo.Problem> getProblems(string status);
    }
}
