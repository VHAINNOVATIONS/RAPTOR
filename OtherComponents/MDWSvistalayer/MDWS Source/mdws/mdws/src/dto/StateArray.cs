using System;
using System.Collections.Generic;
using System.Collections;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class StateArray : AbstractArrayTO
    {
        public StateTO[] states;

        public StateArray() { }

        public StateArray(State[] mdoStates)
        {
            if (mdoStates == null || mdoStates.Length == 0)
            {
                count = 0;
                return;
            }
            ArrayList al = new ArrayList(mdoStates.Length);
            for (int i = 0; i < mdoStates.Length; i++)
            {
                if (mdoStates[i] != null)
                {
                    al.Add(new StateTO(mdoStates[i]));
                }
            }
            states = (StateTO[])al.ToArray(typeof(StateTO));
            count = states.Length;
        }

        public StateArray(SortedList lst)
        {
            if (lst == null || lst.Count == 0)
            {
                count = 0;
                return;
            }
            ArrayList al = new ArrayList(lst.Count);
            IDictionaryEnumerator e = lst.GetEnumerator();
            while (e.MoveNext())
            {
                State s = (State)e.Value;
                if (s != null)
                {
                    al.Add(new StateTO(s));
                }
            }
            states = (StateTO[])al.ToArray(typeof(StateTO));
            count = states.Length;
        }
    }
}
