using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace gov.va.medora.mdo.dao
{
    public abstract class AbstractStates
    {
        public Dictionary<string, AbstractState> States { get; set; }

        public abstract void setState(string id, AbstractState state);
    }

    public abstract class AbstractState
    {
        public object State { get; set; }
    }
}
