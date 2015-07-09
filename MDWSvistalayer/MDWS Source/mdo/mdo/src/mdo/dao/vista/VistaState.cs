using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace gov.va.medora.mdo.dao.vista
{
    public class VistaStates : AbstractStates
    {
        public VistaStates() : base() { }

        public VistaStates(Dictionary<string, AbstractState> states)
        {
            this.States = states;
        }

        public override void setState(string id, AbstractState state)
        {
            if (!this.States.ContainsKey(id))
            {
                this.States.Add(id, state);
            }
            else
            {
                this.States[id] = state;
            }
        }
    }

    public class VistaState : AbstractState
    {
        public VistaState() : base() { }

        public VistaState(Dictionary<string, object> state)
        {
            this.State = state;
        }
    }
}
